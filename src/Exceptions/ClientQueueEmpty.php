<?php

declare(strict_types=1);

namespace PsrMock\Psr18\Exceptions;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use function is_string;

final class ClientQueueEmpty extends Exception implements ClientExceptionInterface
{
    /**
     * @var string
     */
    public const STRING_QUEUE_EMPTY = 'The response queue is empty';

    /**
     * @var string
     */
    public const STRING_QUEUE_EMPTY_FOR_URI = 'The response queue is empty. Unable to resolve request for %s';

    public function __construct(
        ?string $requestMethodAndUri = null,
    ) {
        if (is_string($requestMethodAndUri) && '' !== trim($requestMethodAndUri)) {
            parent::__construct(sprintf(self::STRING_QUEUE_EMPTY_FOR_URI, trim($requestMethodAndUri)));

            return;
        }

        parent::__construct(self::STRING_QUEUE_EMPTY);
    }
}
