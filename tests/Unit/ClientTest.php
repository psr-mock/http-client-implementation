<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use PsrMock\Psr18\Client;
use PsrMock\Psr18\Exceptions\ClientQueueEmpty;
use PsrMock\Psr18\Exceptions\ClientRequestLimitSurpassed;
use PsrMock\Psr18\Exceptions\ClientTotalRequestLimitSurpassed;

it('can return a fallback response', function () {
    $client = new Client();

    $request = $this->createMock(RequestInterface::class);
    $fallback = $this->createMock(ResponseInterface::class);

    $client->setFallbackResponse($fallback);

    expect($client->getResponses())
        ->toBeArray()
        ->toHaveCount(0);

    expect($client->sendRequest($request))
        ->toBe($fallback);
});

it('can return a fallback response after the response queue empties', function () {
    $client = new Client();

    $uri1 = mock(UriInterface::class)->expect(
        __toString: fn () => 'https://example'
    );

    $uri2 = mock(UriInterface::class)->expect(
        __toString: fn () => 'https://somewhere'
    );

    $fallback = $this->createMock(ResponseInterface::class);

    $request1 = mock(RequestInterface::class)->expect(
        getMethod: fn () => 'GET',
        getUri: fn () => $uri1
    );

    $request2 = mock(RequestInterface::class)->expect(
        getMethod: fn () => 'POST',
        getUri: fn () => $uri2
    );

    $client->setFallbackResponse($fallback);

    $client->addResponse('POST', $uri2, $this->createMock(ResponseInterface::class));
    $client->addResponse('GET', $uri1, $this->createMock(ResponseInterface::class));

    expect($client->sendRequests([$request1, $request2, $this->createMock(RequestInterface::class), $this->createMock(RequestInterface::class)]))
        ->toBeArray()
        ->toHaveCount(4);
});

it('can add and get responses', function () {
    $client = new Client();

    $response = $this->createMock(ResponseInterface::class);

    $client->addResponse('GET', 'https://example.com', $response);

    expect($client->getResponses())
        ->toHaveKey('GET https://example.com');

    expect($client->getResponses()['GET https://example.com'])
        ->toBe($response);
});

it('can add and get responses using HTTP Request message', function () {
    $client = new Client();

    $uri = mock(UriInterface::class)->expect(
        __toString: fn () => 'https://example'
    );

    $request = mock(RequestInterface::class)->expect(
        getMethod: fn () => 'GET',
        getUri: fn () => $uri
    );

    $response = $this->createMock(ResponseInterface::class);

    $client->addResponseByRequest($request, $response);

    expect($client->sendRequest($request))
        ->toBe($response);
});

it('can queue and get responses', function () {
    $client = new Client();

    $response1 = $this->createMock(ResponseInterface::class);
    $response2 = $this->createMock(ResponseInterface::class);

    $client->addResponse('GET', 'https://somewhere', $response1);
    $client->addResponse('GET', 'https://elsewhere', $response2);

    expect($client->getResponses())
        ->toBeArray()
        ->toHaveCount(2)
        ->toHaveKey('GET https://somewhere', $response1)
        ->toHaveKey('GET https://elsewhere', $response2);
});

it('throws an exception if no response is found', function () {
    $client = new Client();

    $request = $this->createMock(RequestInterface::class);

    $client->sendRequest($request);
})->throws(ClientQueueEmpty::class, ClientQueueEmpty::STRING_QUEUE_EMPTY);

it('throws an exception if request count exceeds setRequestLimit()', function () {
    $fallback = $this->createMock(ResponseInterface::class);

    $client = new Client();
    $client->setRequestLimit(1);
    $client->setFallbackResponse($fallback);

    $client->sendRequests([
        $this->createMock(RequestInterface::class),
        $this->createMock(RequestInterface::class)
    ]);
})->throws(ClientTotalRequestLimitSurpassed::class, sprintf(ClientTotalRequestLimitSurpassed::STRING_REACHED_WITH_LIMIT, 1, 'GET https://example'));

it('throws an exception if request count exceeds limit set with addResponse()', function () {
    $client = new Client();

    $client->addResponse('GET', 'https://example', $this->createMock(ResponseInterface::class), 1);

    $requestUri = mock(UriInterface::class)->expect(
        __toString: fn () => 'https://example'
    );

    $request = mock(RequestInterface::class)->expect(
        getMethod: fn () => 'GET',
        getUri: fn () => $requestUri
    );

    $client->sendRequests([
        $request,
        $request
    ]);
})->throws(ClientRequestLimitSurpassed::class, sprintf(ClientRequestLimitSurpassed::STRING_REQUEST_URI_WITH_LIMIT, 1, 'GET https://example'));

it('throws an exception if request count exceeds limit set with addResponseByRequest()', function () {
    $client = new Client();

    $requestUri = mock(UriInterface::class)->expect(
        __toString: fn () => 'https://example'
    );

    $request = mock(RequestInterface::class)->expect(
        getMethod: fn () => 'GET',
        getUri: fn () => $requestUri
    );

    $client->addResponseByRequest($request, $this->createMock(ResponseInterface::class), 1);

    $client->sendRequests([
        $request,
        $request
    ]);
})->throws(ClientRequestLimitSurpassed::class, sprintf(ClientRequestLimitSurpassed::STRING_REQUEST_URI_WITH_LIMIT, 1, 'GET https://example'));

it('can send a request and record the exchange', function () {
    $client = new Client();

    $response = $this->createMock(ResponseInterface::class);
    $request = mock(RequestInterface::class)->expect(
        getMethod: fn () => 'GET',
        getUri: fn () => 'https://example',
    );

    $timeline = $client->getTimeline();

    expect($timeline)
        ->toBeArray()
        ->toHaveCount(0);

    $client->addResponse('GET', 'https://example', $response);

    expect($client->sendRequest($request))
        ->toBe($response);

    $timeline = $client->getTimeline();
    // var_dump($timeline); exit;

    expect($timeline)
        ->toBeArray()
        ->toHaveCount(1);

    expect($timeline[0])
        ->toBeArray()
        ->toHaveKey('request', $request)
        ->toHaveKey('response', $response)
        ->toHaveKey('count', 1);
});
