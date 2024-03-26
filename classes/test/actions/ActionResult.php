<?php

namespace JaxWilko\Hugo\Classes\Test\Actions;

use JaxWilko\Hugo\Classes\Test\Actions\Contracts\ActionInterface;

class ActionResult implements ActionInterface
{
    public function __construct(
        public readonly int $status,
        public readonly mixed $value = null
    ) {}

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
}
