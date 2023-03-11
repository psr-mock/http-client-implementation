<?php

declare(strict_types=1);

namespace PsrMock\Psr18\Contracts;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

interface ClientContract extends ClientInterface
{
    public function addResponse(string $method, string $url, ResponseInterface $response): void;

    public function getHistory(): HistoryContract;

    /**
     * Get the queue of responses to return.
     *
     * @return array<ResponseInterface>
     */
    public function getQueue(): array;

    /**
     * Get the responses to return.
     *
     * @return array<ResponseInterface>
     */
    public function getResponses(): array;

    public function queueResponse(ResponseInterface $response): void;

    public function sendRequest(RequestInterface $request): ResponseInterface;

    public function setHistory(HistoryContract $historyContract): void;

    /**
     * Set the queue of responses to return.
     *
     * @param array<ResponseInterface> $queue
     */
    public function setQueue(array $queue): void;

    /**
     * Set the responses to return.
     *
     * @param array<ResponseInterface> $responses
     */
    public function setResponses(array $responses): void;
}
