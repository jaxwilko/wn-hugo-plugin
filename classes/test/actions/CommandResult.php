<?php

namespace JaxWilko\Hugo\Classes\Test\Actions;

use JaxWilko\Hugo\Classes\Test\Actions\Contracts\LogItemInterface;

class CommandResult implements LogItemInterface
{
    public function __construct(
        public readonly string $command,
        public readonly array $args,
        public readonly ActionResult|ExitAction|null $result = null
    ) {}
}
