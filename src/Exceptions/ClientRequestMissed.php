<?php

declare(strict_types=1);

namespace PsrMock\Psr18\Exceptions;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use function is_string;

final class ClientRequestMissed extends Exception implements ClientExceptionInterface
{
    public function __construct(
        ?string $requestMethodAndUri = null,
    ) {
        if (is_string($requestMethodAndUri) && '' !== trim($requestMethodAndUri)) {
            parent::__construct(sprintf('There were no queued matching responses for the request: %s', trim($requestMethodAndUri)));

            return;
        }

        parent::__construct('There were no queued matching responses for the request');
    }
}
