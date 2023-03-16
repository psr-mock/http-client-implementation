<?php

declare(strict_types=1);

namespace PsrMock\Psr18;

use Exception;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use PsrMock\Psr18\Contracts\ClientContract;
use PsrMock\Psr18\Exceptions\{ClientQueueEmpty, ClientRequestLimitSurpassed, ClientRequestMissed, ClientTotalRequestLimitSurpassed};

final class Client implements ClientContract
{
    /**
     * @param array<string,ResponseInterface> $responses
     * @param array<int,ResponseInterface>    $wildcardResponses
     * @param null|ResponseInterface          $fallbackResponse
     * @param null|int                        $requestLimit
     */
    public function __construct(
        private array $responses = [],
        private array $wildcardResponses = [],
        private ?ResponseInterface $fallbackResponse = null,
        private ?int $requestLimit = null,
    ) {
    }

    public function addResponse(string $method, UriInterface | string $url, ResponseInterface $response, ?int $limit = null): void
    {
        if ($url instanceof UriInterface) {
            $url = (string) $url;
        }

        $key = strtoupper($method) . ' ' . $url;

        $this->responses[$key] = $response;

        if (null !== $limit) {
            $this->limits[$key] = $limit;
        }
    }

    public function addResponseByRequest(RequestInterface $request, ResponseInterface $response, ?int $limit = null): void
    {
        $key = (string) $request->getMethod() . ' ' . (string) $request->getUri();

        $this->responses[$key] = $response;

        if (null !== $limit) {
            $this->limits[$key] = $limit;
        }
    }

    public function addResponseWildcard(ResponseInterface $response): void
    {
        $this->wildcardResponses[] = $response;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getTimeline(): array
    {
        return $this->history;
    }

    public function getWildcardResponses(): array
    {
        return $this->wildcardResponses;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $key = (string) $request->getMethod() . ' ' . (string) $request->getUri();

        if (null !== $this->requestLimit && $this->requestCount >= $this->requestLimit) {
            throw new ClientTotalRequestLimitSurpassed($this->requestLimit);
        }

        ++$this->requestCount;

        if (isset($this->wildcardResponses[$this->requestCount - 1])) {
            $response = $this->wildcardResponses[$this->requestCount - 1];

            if ($response instanceof ResponseInterface) {
                $this->counter[$key] ??= 0;
                $this->counter[$key] = (int) $this->counter[$key] + 1;
                $this->history[]     = ['request' => $request, 'response' => $response, 'count' => $this->counter[$key], 'when' => time()];

                return $response;
            }
        }

        if (isset($this->responses[$key])) {
            $response = $this->responses[$key];

            if ($response instanceof ResponseInterface) {
                $this->counter[$key] ??= 0;

                if (isset($this->limits[$key]) && $this->counter[$key] >= $this->limits[$key]) {
                    throw new ClientRequestLimitSurpassed($key, $this->limits[$key]);
                }

                $this->counter[$key] = (int) $this->counter[$key] + 1;
                $this->history[]     = ['request' => $request, 'response' => $response, 'count' => $this->counter[$key], 'when' => time()];

                return $response;
            }
        }

        if (isset($this->fallbackResponse)) {
            return $this->fallbackResponse;
        }

        if ([] === $this->wildcardResponses && [] === $this->responses) {
            throw new ClientQueueEmpty($key);
        }

        throw new ClientRequestMissed($key);
    }

    public function sendRequests(array $requests): array
    {
        $responses = [];

        foreach ($requests as $request) {
            $responses[] = $this->sendRequest($request);
        }

        return $responses;
    }

    public function setFallbackResponse(ResponseInterface $response): void
    {
        $this->fallbackResponse = $response;
    }

    public function setRequestLimit(?int $limit = null): void
    {
        $this->requestLimit = $limit;
    }

    /**
     * @var array<string,int>
     */
    private array $counter = [];

    /**
     * @var array<int,array{request:RequestInterface,response:ResponseInterface,count:int,when:int}>
     */
    private array $history = [];

    /**
     * @var array<string,int>
     */
    private array $limits     = [];
    private int $requestCount = 0;
}
