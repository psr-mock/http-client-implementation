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

    $request = Mockery::mock(RequestInterface::class);
    $request->shouldReceive('getMethod')->andReturn('GET')->shouldReceive('getUri')->andReturn('https://example.com');

    $fallback = Mockery::mock(ResponseInterface::class);
    $fallback->shouldReceive('getStatusCode')->andReturn(200);

    $client->setFallbackResponse($fallback);

    expect($client->getResponses())
        ->toBeArray()
        ->toHaveCount(0);

    expect($client->sendRequest($request))
        ->toBe($fallback);
});

it('can return a fallback response after the response queue empties', function () {
    $client = new Client();

    $uri1 = Mockery::mock(UriInterface::class);
    $uri1->shouldReceive('__toString')->andReturn('https://example');

    $uri2 = Mockery::mock(UriInterface::class);
    $uri2->shouldReceive('__toString')->andReturn('https://somewhere');

    $request1 = Mockery::mock(RequestInterface::class);
    $request1->shouldReceive('getMethod')->andReturn('GET');
    $request1->shouldReceive('getUri')->andReturn($uri1);

    $request2 = Mockery::mock(RequestInterface::class);
    $request2->shouldReceive('getMethod')->andReturn('POST');
    $request2->shouldReceive('getUri')->andReturn($uri2);

    $request3 = Mockery::mock(RequestInterface::class);
    $request3->shouldReceive('getMethod')->andReturn('PATCH');
    $request3->shouldReceive('getUri')->andReturn($uri1);

    $request4 = Mockery::mock(RequestInterface::class);
    $request4->shouldReceive('getMethod')->andReturn('PUT');
    $request4->shouldReceive('getUri')->andReturn($uri2);

    $fallback = Mockery::mock(ResponseInterface::class);

    $client->setFallbackResponse($fallback);

    $client->addResponse('GET', $uri1, Mockery::mock(ResponseInterface::class));
    $client->addResponse('POST', $uri2, Mockery::mock(ResponseInterface::class));

    expect($client->sendRequests([$request1, $request2, $request3, $request4]))
        ->toBeArray()
        ->toHaveCount(4);
});

it('can add and get responses', function () {
    $client = new Client();

    $response = Mockery::mock(ResponseInterface::class);

    $client->addResponse('GET', 'https://example.com', $response);

    expect($client->getResponses())
        ->toHaveKey('GET https://example.com');

    expect($client->getResponses()['GET https://example.com'])
        ->toBe($response);
});

it('can add and get responses using HTTP Request message', function () {
    $client = new Client();

    $uri = Mockery::mock(UriInterface::class);
    $uri->shouldReceive('__toString')->andReturn('https://example.com');
    $request = Mockery::mock(RequestInterface::class);
    $request->shouldReceive('getMethod')->andReturn('GET')->shouldReceive('getUri')->andReturn($uri);
    $response = Mockery::mock(ResponseInterface::class);

    $client->addResponseByRequest($request, $response);

    expect($client->sendRequest($request))
        ->toBe($response);
});

it('can queue and get responses', function () {
    $client = new Client();

    $response1 = Mockery::mock(ResponseInterface::class);
    $response2 = Mockery::mock(ResponseInterface::class);

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

    $request = Mockery::mock(RequestInterface::class);
    $request->shouldReceive('getMethod')->andReturn('GET');
    $request->shouldReceive('getUri')->andReturn('https://example.com');

    $client->sendRequest($request);
})->throws(ClientQueueEmpty::class, ClientQueueEmpty::STRING_QUEUE_EMPTY);

it('throws an exception if request count exceeds setRequestLimit()', function () {
    $fallback = Mockery::mock(ResponseInterface::class);

    $client = new Client();
    $client->setRequestLimit(1);
    $client->setFallbackResponse($fallback);

    $uri1 = Mockery::mock(UriInterface::class);
    $uri1->shouldReceive('__toString')->andReturn('https://example');

    $uri2 = Mockery::mock(UriInterface::class);
    $uri2->shouldReceive('__toString')->andReturn('https://somewhere');

    $request1 = Mockery::mock(RequestInterface::class);
    $request1->shouldReceive('getMethod')->andReturn('GET');
    $request1->shouldReceive('getUri')->andReturn($uri1);

    $request2 = Mockery::mock(RequestInterface::class);
    $request2->shouldReceive('getMethod')->andReturn('POST');
    $request2->shouldReceive('getUri')->andReturn($uri2);

    $client->sendRequests([$request1, $request2]);
})->throws(ClientTotalRequestLimitSurpassed::class, sprintf(ClientTotalRequestLimitSurpassed::STRING_REACHED_WITH_LIMIT, 1, 'GET https://example'));

it('throws an exception if request count exceeds limit set with addResponse()', function () {
    $client = new Client();

    $client->addResponse('GET', 'https://example', Mockery::mock(ResponseInterface::class), 1);

    $requestUri = Mockery::mock(UriInterface::class);
    $requestUri->shouldReceive('__toString')->andReturn('https://example');

    $request = Mockery::mock(RequestInterface::class);
    $request->shouldReceive('getMethod')->andReturn('GET')->shouldReceive('getUri')->andReturn($requestUri);

    $client->sendRequests([
        $request,
        $request
    ]);
})->throws(ClientRequestLimitSurpassed::class, sprintf(ClientRequestLimitSurpassed::STRING_REQUEST_URI_WITH_LIMIT, 1, 'GET https://example'));

it('throws an exception if request count exceeds limit set with addResponseByRequest()', function () {
    $client = new Client();

    $requestUri = Mockery::mock(UriInterface::class);
    $requestUri->shouldReceive('__toString')->andReturn('https://example');

    $request = Mockery::mock(RequestInterface::class);
    $request->shouldReceive('getMethod')->andReturn('GET')->shouldReceive('getUri')->andReturn($requestUri);

    $client->addResponseByRequest($request, Mockery::mock(ResponseInterface::class), 1);

    $client->sendRequests([
        $request,
        $request
    ]);
})->throws(ClientRequestLimitSurpassed::class, sprintf(ClientRequestLimitSurpassed::STRING_REQUEST_URI_WITH_LIMIT, 1, 'GET https://example'));

it('can send a request and record the exchange', function () {
    $client = new Client();

    $response = Mockery::mock(ResponseInterface::class);
    $request = Mockery::mock(RequestInterface::class);
    $request->shouldReceive('getMethod')->andReturn('GET')->shouldReceive('getUri')->andReturn('https://example');

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
