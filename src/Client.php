<?php

declare(strict_types=1);

namespace PsrMock\Psr18;

use Exception;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use PsrMock\Psr18\Contracts\{ClientContract, ExchangeContract, HistoryContract};

final class Client implements ClientContract
{
    /**
     * @param array<ResponseInterface> $responses
     * @param array<ResponseInterface> $queue
     */
    public function __construct(
        private array $responses = [],
        private array $queue = [],
    ) {
        $this->historyContract = new History();
    }

    private function exchange(RequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $url    = $request->getUri()->__toString();
        $key    = $method . ' ' . $url;

        if (isset($this->responses[$key])) {
            return $this->responses[$key];
        }

        if ([] !== $this->queue) {
            return array_shift($this->queue);
        }

        throw new Exception('No response found for ' . $key);
    }

    private function record(ExchangeContract $exchangeContract): ResponseInterface
    {
        $this->historyContract->add($exchangeContract);

        return $exchangeContract->getResponse();
    }

    public function addResponse(string $method, string $url, ResponseInterface $response): void
    {
        $key                   = $method . ' ' . $url;
        $this->responses[$key] = $response;
    }

    public function getHistory(): HistoryContract
    {
        return $this->historyContract;
    }

    public function getQueue(): array
    {
        return $this->queue;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function queueResponse(ResponseInterface $response): void
    {
        $this->queue[] = $response;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->record(new Exchange($request, $this->exchange($request)));
    }

    public function setHistory(HistoryContract $historyContract): void
    {
        $this->historyContract = $historyContract;
    }

    public function setQueue(array $queue): void
    {
        $this->queue = $queue;
    }

    public function setResponses(array $responses): void
    {
        $this->responses = $responses;
    }
    private HistoryContract $historyContract;
}
