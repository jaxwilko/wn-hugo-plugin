<?php

namespace JaxWilko\Hugo\Classes\Automation\Actions\Contracts;

interface ActionResultInterface
{
    public function __construct(int $status, mixed $value = null);

    public function successful(): bool;

    public function getStatus(): int;

    public function getValue(): mixed;

    public function getTimestamp(): float;
}
