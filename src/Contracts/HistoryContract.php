<?php

declare(strict_types=1);

namespace PsrMock\Psr18\Contracts;

use Iterator;

/**
 * @extends \Iterator<int, ExchangeContract|false>
 */
interface HistoryContract extends Iterator
{
    public function add(ExchangeContract $exchangeContract): void;

    public function current(): ExchangeContract | false;

    public function key(): int;

    public function next(): void;

    public function rewind(): void;

    public function valid(): bool;
}
