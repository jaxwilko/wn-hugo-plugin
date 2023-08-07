<?php

namespace JaxWilko\Hugo\Classes\Test\Actions;

use JaxWilko\Hugo\Classes\Test\Actions\Contracts\LogItemInterface;

class Screenshot implements LogItemInterface
{
    public function __construct(
        public readonly string $path,
        public readonly string $label
    ) {}
}
