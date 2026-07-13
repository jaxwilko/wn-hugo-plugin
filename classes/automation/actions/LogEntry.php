<?php

namespace JaxWilko\Hugo\Classes\Automation\Actions;

use JaxWilko\Hugo\Classes\Automation\Actions\Contracts\LogItemInterface;

class LogEntry implements LogItemInterface
{
    public function __construct(
        public readonly string $message
    ) {}
}
