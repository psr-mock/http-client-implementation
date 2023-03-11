<?php

declare(strict_types=1);

namespace PsrMock\Psr18;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use PsrMock\Psr18\Contracts\ExchangeContract;

final class Exchange implements ExchangeContract
{
    public function __construct(
        private RequestInterface $request,
        private ResponseInterface $response,
    ) {
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
