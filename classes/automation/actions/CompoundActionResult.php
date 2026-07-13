<?php

namespace JaxWilko\Hugo\Classes\Automation\Actions;

use JaxWilko\Hugo\Classes\Automation\Actions\Contracts\CompoundActionResultInterface;

readonly class CompoundActionResult extends ActionResult implements CompoundActionResultInterface
{
    public function __construct(
        int $status,
        mixed $value = null,
        public mixed $results = null
    ) {
        parent::__construct($status, $value);
    }

    public function getNestedResults(): array
    {
        return $this->results;
    }
}
