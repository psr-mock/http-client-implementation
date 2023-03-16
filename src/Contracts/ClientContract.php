<?php

declare(strict_types=1);

namespace PsrMock\Psr18\Contracts;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};

interface ClientContract extends ClientInterface
{
    /**
     * Queue a response for the client to return to matching requests.
     *
     * @param string              $method   The HTTP method to match.
     * @param string|UriInterface $url      The URL to match.
     * @param ResponseInterface   $response The response to return.
     * @param null|int            $limit    The maximum of times allowed to return the response.
     */
    public function addResponse(string $method, UriInterface | string $url, ResponseInterface $response, ?int $limit = null): void;

    /**
     * Queue a response for the client to return to matching requests.
     *
     * @param RequestInterface  $request  The request to match.
     * @param ResponseInterface $response The response to return.
     * @param null|int          $limit    The maximum of times allowed to return the response.
     */
    public function addResponseByRequest(RequestInterface $request, ResponseInterface $response, ?int $limit = null): void;

    /**
     * Queue a response for the client to return to all unmatched requests, in the order they are added.
     * Each queued wildcard response will be returned once, and then the client will return the fallback response.
     *
     * @param ResponseInterface $response The response to return.
     */
    public function addResponseWildcard(ResponseInterface $response): void;

    /**
     * Returns an array representing all the queued responses. Note that the responses are not returned in the order they were added.
     *
     * @return array<string,ResponseInterface>
     */
    public function getResponses(): array;

    /**
     * Returns an array representing the timeline of requests made, along with the responses returned.
     *
     * @return array<int,array{request:RequestInterface,response:ResponseInterface,count:int,when:int}>
     */
    public function getTimeline(): array;

    /**
     * Returns an array representing all the remaining queued wildcard responses.
     *
     * @return array<int,ResponseInterface>
     */
    public function getWildcardResponses(): array;

    /**
     * "Sends" a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request The request to send.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface;

    /**
     * "Sends" a series of PSR-7 requests and returns an array of PSR-7 responses, in matching order.
     *
     * @param array<int,RequestInterface> $requests
     *
     * @return array<int,ResponseInterface>
     */
    public function sendRequests(array $requests): array;

    /**
     * Assigns a fallback response to return when no other responses are available.
     *
     * @param ResponseInterface $response The response to return.
     */
    public function setFallbackResponse(ResponseInterface $response): void;

    /**
     * Assigns a limit to the number of requests that can be made in total.
     *
     * @param null|int $limit The maximum number of requests allowed.
     */
    public function setRequestLimit(?int $limit = null): void;
}
