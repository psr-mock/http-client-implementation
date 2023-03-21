<?php

declare(strict_types=1);

namespace PsrMock\Psr18\Exceptions;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use function is_string;

final class ClientRequestLimitSurpassed extends Exception implements ClientExceptionInterface
{
    /**
     * @var string
     */
    public const STRING_REQUEST_URI_WITH_LIMIT = 'The request limit of %d was surpassed for the request: %s';
    /**
     * @var string
     */
    public const STRING_REQUEST_LIMIT = 'The request limit was surpassed for the request: %s';
    /**
     * @var string
     */
    public const STRING_REQUEST_LIMIT_WITHOUT_URI = 'The request limit of %d was surpassed';
    /**
     * @var string
     */
    public const STRING_REQUEST_LIMIT_WITHOUT_URI_AND_LIMIT = 'The request limit was surpassed';

    public function __construct(
        ?string $requestMethodAndUri = null,
        ?int $limit = null,
    ) {
        if (is_string($requestMethodAndUri) && '' !== trim($requestMethodAndUri)) {
            if (\is_int($limit)) {
                parent::__construct(sprintf(self::STRING_REQUEST_URI_WITH_LIMIT, $limit, trim($requestMethodAndUri)));
                return;
            }

            parent::__construct(sprintf(self::STRING_REQUEST_LIMIT, trim($requestMethodAndUri)));
            return;
        }

        if (\is_int($limit)) {
            parent::__construct(sprintf(self::STRING_REQUEST_LIMIT_WITHOUT_URI, $limit));
            return;
        }

        parent::__construct(self::STRING_REQUEST_LIMIT_WITHOUT_URI_AND_LIMIT);
    }
}
