<?php

declare(strict_types=1);

namespace PsrMock\Psr18;

use Exception;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use PsrMock\Psr18\Contracts\ClientContract;

final class Client implements ClientContract
{
    /**
     * @var array<int,array{request:RequestInterface,response:ResponseInterface,count:int,when:int}> $history
     */
    private array $history = [];
    /**
     * @var array<string,int> $counter
     */
    private array $counter = [];
    /**
     * @var array<string,int> $limits
     */
    private array $limits  = [];

    private int $requestCount = 0;

    /**
     * @param array<string,ResponseInterface> $responses
     */
    public function __construct(
        private array $responses = [],
        private ?ResponseInterface $fallbackResponse = null,
        private ?int $requestLimit = null
    ) {
    }

    public function setRequestLimit(?int $limit = null): void
    {
        $this->requestLimit = $limit;
    }

    public function setFallbackResponse(ResponseInterface $response) : void
    {
        $this->fallbackResponse = $response;
    }

    /**
     * Get the timeline of requests and responses.
     *
     * @return array<int,array{request:RequestInterface,response:ResponseInterface,count:int,when:int}>
     */
    public function getTimeline(): array
    {
        return $this->history;
    }

    /**
     * Get the responses.
     *
     * @return array<string,ResponseInterface>
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function addResponse(string $method, UriInterface|string $url, ResponseInterface $response, ?int $times = null): void
    {
        if ($url instanceof UriInterface) {
            $url = (string) $url;
        }

        $key = strtoupper($method) . ' ' . $url;

        $this->responses[$key] = $response;

        if ($times !== null) {
            $this->limits[$key] = $times;
        }
    }

    public function addResponseByRequest(RequestInterface $request, ResponseInterface $response, ?int $times = null): void
    {
        $key = (string) $request->getMethod() . ' ' . (string) $request->getUri();

        $this->responses[$key] = $response;

        if ($times !== null) {
            $this->limits[$key] = $times;
        }
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $key = (string) $request->getMethod() . ' ' . (string) $request->getUri();

        if ($this->requestLimit !== null && $this->requestCount >= $this->requestLimit) {
            throw new Exception('Exceeded session request limit of ' . $this->requestLimit);
        }

        ++$this->requestCount;

        if (isset($this->responses[$key])) {
            $response = $this->responses[$key];

            if ($response instanceof ResponseInterface) {
                $this->counter[$key] ??= 0;

                if (isset($this->limits[$key]) && $this->counter[$key] >= $this->limits[$key]) {
                    throw new Exception('Exceeded request limit of ' . (string) $this->limits[$key] . ' for ' . $key);
                }

                $this->counter[$key] = (int) $this->counter[$key] + 1;
                $this->history[] = ['request' => $request, 'response' => $response, 'count' => $this->counter[$key], 'when' => time()];

                return $response;
            }
        }

        if (isset($this->fallbackResponse)) {
            return $this->fallbackResponse;
        }

        throw new Exception('No response found for ' . $key);
    }

    /**
     * @param array<int,RequestInterface> $requests
     *
     * @return array<int,ResponseInterface>
     */
    public function sendRequests(array $requests): array
    {
        $responses = [];

        foreach ($requests as $request) {
            $responses[] = $this->sendRequest($request);
        }

        return $responses;
    }
}
