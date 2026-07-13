<?php

namespace JaxWilko\Hugo\Classes\Automation\Actions;

use JaxWilko\Hugo\Classes\Automation\Actions\Contracts\LogItemInterface;

readonly class CommandResult implements LogItemInterface
{
    public function __construct(
        public string $command,
        public array $args,
        public ActionResult|CompoundActionResult|ExitAction|null $result = null
    ) {}
}
