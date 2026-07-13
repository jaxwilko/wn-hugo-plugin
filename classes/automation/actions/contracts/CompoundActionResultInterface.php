<?php

namespace JaxWilko\Hugo\Classes\Automation\Actions\Contracts;

interface CompoundActionResultInterface extends ActionResultInterface
{
    public function __construct(int $status, mixed $value = null, mixed $result = null);

    public function getNestedResults(): array;
}
