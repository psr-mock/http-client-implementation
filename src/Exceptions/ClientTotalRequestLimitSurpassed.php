<?php

declare(strict_types=1);

namespace PsrMock\Psr18\Exceptions;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use function is_string;

final class ClientTotalRequestLimitSurpassed extends Exception implements ClientExceptionInterface
{
    /**
     * @var string
     */
    public const STRING_REACHED_WITH_LIMIT = 'The request limit of %d was surpassed';
    /**
     * @var string
     */
    public const STRING_REACHED = 'The request limit was surpassed';

    public function __construct(
        ?int $limit = null,
    ) {
        if (\is_int($limit)) {
            parent::__construct(sprintf(self::STRING_REACHED_WITH_LIMIT, $limit));
            return;
        }

        parent::__construct(self::STRING_REACHED);
    }
}
