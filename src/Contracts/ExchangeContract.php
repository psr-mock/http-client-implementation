<?php

declare(strict_types=1);

namespace PsrMock\Psr18\Contracts;

use Psr\Http\Message\{RequestInterface, ResponseInterface};

interface ExchangeContract
{
    public function getRequest(): RequestInterface;

    public function getResponse(): ResponseInterface;
}
