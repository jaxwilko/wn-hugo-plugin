<?php

namespace JaxWilko\Hugo\Classes\Automation\Actions;

use JaxWilko\Hugo\Classes\Automation\Actions\Contracts\LogItemInterface;

class Screenshot implements LogItemInterface
{
    public function __construct(
        public readonly string $path,
        public readonly string $label
    ) {}
}
