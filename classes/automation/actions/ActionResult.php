<?php

namespace JaxWilko\Hugo\Classes\Automation\Actions;

use JaxWilko\Hugo\Classes\Automation\Actions\Contracts\ActionResultInterface;

readonly class ActionResult implements ActionResultInterface
{
    public float $timestamp;

    public function __construct(
        public int $status,
        public mixed $value = null
    ) {
        $this->timestamp = microtime(true);
    }

    public function successful(): bool
    {
        return $this->status === 0;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }
}
