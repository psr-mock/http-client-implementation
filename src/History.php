<?php

declare(strict_types=1);

namespace PsrMock\Psr18;

use PsrMock\Psr18\Contracts\{ExchangeContract, HistoryContract};

use function is_int;

final class History implements HistoryContract
{
    /**
     * @param array<ExchangeContract> $records
     */
    public function __construct(
        private array $records = [],
    ) {
    }

    public function add(ExchangeContract $exchangeContract): void
    {
        $this->records[] = $exchangeContract;
    }

    public function current(): ExchangeContract | false
    {
        return current($this->records);
    }

    public function key(): int
    {
        $key = key($this->records);

        if (! is_int($key)) {
            return 0;
        }

        return $key;
    }

    public function next(): void
    {
        next($this->records);
    }

    public function rewind(): void
    {
        reset($this->records);
    }

    public function valid(): bool
    {
        return null !== key($this->records);
    }
}
