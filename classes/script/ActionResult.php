<?php

namespace JaxWilko\Hugo\Classes\Script;

class ActionResult
{
    public function __construct(
        protected mixed $result,
        protected array $logs = [],
        protected array $screenshots = []
    ) {}

    public static function fromResult(
        ActionResult $actionResult,
        mixed $result,
        array $logs = [],
        array $screenshots = []
    ): ActionResult {
        return new static($result, $actionResult->getLogs() + $logs, $actionResult->getScreenshots() + $screenshots);
    }

    public static function fromResults(array $actionResults): ActionResult
    {
        foreach ($actionResults as $result) {

        }
        
        return new static($result, $actionResult->getLogs() + $logs, $actionResult->getScreenshots() + $screenshots);
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getScreenshots(): array
    {
        return $this->screenshots;
    }
}
