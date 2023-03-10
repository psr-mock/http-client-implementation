<?php

declare(strict_types=1);

namespace PsrMock\Psr18;

final class History implements \Iterator
{
    public function __construct(
        private array $records = [],
    ) {
    }

    public function rewind (): void {
      reset($this->records);
    }

    public function current (): Exchange {
      return current($this->records);
    }

    public function key () {
      return key($this->records);
    }

    public function next (): void {
      next($this->records);
    }

    public function valid (): bool {
      return key($this->records) !== null;
    }

    public function add(Exchange $exchange): void
    {
        $this->records[] = $exchange;
    }
}
