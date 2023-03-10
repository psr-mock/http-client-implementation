<?php

declare(strict_types=1);

namespace PsrMock\Psr18;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

final class Client implements ClientInterface
{
    private History $history;

    public function __construct(
        private array $responses = [],
        private array $queue = [],
    ) {
        $this->history = new History();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->record(new Exchange($request, $this->exchange($request)));
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getQueue(): array
    {
        return $this->queue;
    }

    public function getHistory(): History
    {
        return $this->history;
    }

    public function setResponses(array $responses): void
    {
        $this->responses = $responses;
    }

    public function setQueue(array $queue): void
    {
        $this->queue = $queue;
    }

    public function setHistory(History $history): void
    {
        $this->history = $history;
    }

    public function addResponse(string $method, string $url, ResponseInterface $response): void
    {
        $key = $method . ' ' . $url;
        $this->responses[$key] = $response;
    }

    public function queueResponse(ResponseInterface $response): void
    {
        $this->queue[] = $response;
    }

    private function record(Exchange $exchange): ResponseInterface
    {
        $this->history->add($exchange);
        return $exchange->getResponse();
    }

    private function exchange(RequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $url = $request->getUri()->__toString();
        $key = $method . ' ' . $url;

        if (isset($this->responses[$key])) {
            return $this->responses[$key];
        }

        if ([] !== $this->queue) {
            return array_shift($this->queue);
        }

        throw new \Exception('No response found for ' . $key);
    }
}
