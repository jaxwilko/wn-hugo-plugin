<?php

namespace JaxWilko\Hugo\Classes\Test\Actions;

use JaxWilko\Hugo\Classes\Test\Actions\Contracts\LogItemInterface;

class LogEntry implements LogItemInterface
{
    public function __construct(
        public readonly string $message
    ) {}
}
