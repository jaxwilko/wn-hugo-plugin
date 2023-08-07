<?php

namespace JaxWilko\Hugo\Classes\Test\Actions\Contracts;

interface ActionInterface
{
    public function __construct(int $status, mixed $value = null);

    public function successful(): bool;

    public function getStatus(): int;

    public function getValue(): mixed;
}
