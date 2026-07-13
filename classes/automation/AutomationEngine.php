<?php

namespace jaxwilko\hugo\classes\automation;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use JaxWilko\Hugo\Classes\Automation\Actions\CommandResult;
use jaxwilko\hugo\classes\automation\actions\CompoundActionResult;
use JaxWilko\Hugo\Classes\Automation\Actions\Contracts\ActionResultInterface;
use JaxWilko\Hugo\Classes\Automation\Actions\ActionResult;
use JaxWilko\Hugo\Classes\Automation\Actions\Contracts\LogItemInterface;
use JaxWilko\Hugo\Classes\Automation\Actions\ExitAction;
use JaxWilko\Hugo\Classes\Automation\Actions\LogEntry;
use JaxWilko\Hugo\Classes\Automation\Actions\Screenshot;

class AutomationEngine
{
    public const int STATUS_OKAY = 0;
    public const int STATUS_GENERAL_ERROR = 1;
    public const int STATUS_UNCAUGHT_ERROR = 2;
    public const int STATUS_NO_EXIT_ERROR = 3;

    protected array $variables = [];

    protected array $config = [];
    protected array $log = [];

    protected float $startedAt;
    protected float $finishedAt;

    protected ?ExitAction $exit = null;

    public function __construct(
        protected HugoWebDriver $webDriver,
        protected bool $verbose,
        protected string $scriptId
    ) {}

    public static function init(HugoWebDriver $webDriver, bool $verbose = false): static
    {
        return new static($webDriver, $verbose, str_replace('.', '', (string) microtime(true)));
    }

    public function run(string $url, array $config, bool $autoScreenshot = false): static
    {
        if ($autoScreenshot) {
            $config = $this->addScreenshotCommands($config);
            $config = [
                [
                    'label' => 'After Navigation Screenshot',
                    '_group' => 'screenshot'
                ],
                ...$config
            ];
        }

        // Run the test but first navigate to the page
        $config = [
            [
                'url' => $url,
                '_group' => 'nav'
            ],
            ...$config
        ];

        $this->startedAt = microtime(true);

        $this->config = $this->execute($config);

        $this->finishedAt = microtime(true);

        return $this;
    }

    public function addScreenshotCommands(array $config, bool $addIndexes = true): array
    {
        $newConfig = [];
        foreach ($config as $index => $item) {
            if ($addIndexes) {
                $item['original_index'] = $index;
            }

            // Add screenshots for nested paths
            if ($item['_group'] === 'ifStatement') {
                $item['condition'] = $this->addScreenshotCommands($item['condition']);
                $item['then'] = $this->addScreenshotCommands($item['then'] ?? []);
                $item['else'] = $this->addScreenshotCommands($item['else'] ?? []);
            }

            if ($item['_group'] === 'set') {
                $item['value'] = $this->addScreenshotCommands($item['value']);
            }

            $newConfig[] = $item;

            if (!in_array($item['_group'], ['screenshot', 'ifStatement', 'set'])) {
                $newConfig[] = [
                    'label' => 'After ' . $item['_group'] . ' Screenshot',
                    '_group' => 'screenshot'
                ];
            }
        }

        return $newConfig;
    }

    public function execute(array $config): array
    {
        foreach ($config as $index => $action) {
            $method = $action['_group'];
            unset($action['_group'], $action['original_index']);

            // Catch exits from other call stacks
            foreach ($this->log as $logItem) {
                if (isset($logItem->result) && $logItem->result instanceof ExitAction) {
                    $this->exit = $logItem->result;
                    return $config;
                }
            }

            if ($this->verbose) {
                dump(['calling' => $method, 'timestamp' => date('Y-m-d H:i:s'), 'args' => $action]);
            }

            // Execute config as command
            try {
                $config[$index]['result'] = $this->{$method}(...$action);
            } catch (\Throwable $e) {
                $config[$index]['result'] = new ActionResult(static::STATUS_UNCAUGHT_ERROR, $this->errorMessage($e));
            }

            // Ignore void responses
            if (!$config[$index]['result']) {
                continue;
            }

            $this->log(new CommandResult($method, $action, $config[$index]['result']));

            if ($config[$index]['result'] instanceof ExitAction) {
                $this->exit = $config[$index]['result'];
                return $config;
            }
        }

        return $config;
    }

    public function getLog(): array
    {
        return $this->log;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getStartedAt(): float
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): float
    {
        return $this->finishedAt;
    }

    public function getExit(): int
    {
        if (!$this->exit) {
            return static::STATUS_NO_EXIT_ERROR;
        }

        return $this->exit->getStatus();
    }

    protected function errorMessage(\Throwable $e): string
    {
        return sprintf('%s @ %s:%s', $e->getMessage(), $e->getFile(), $e->getLine());
    }

    protected function log(LogItemInterface|string $message, mixed ...$args): static
    {
        $this->log[] = $message instanceof Screenshot || $message instanceof CommandResult || $message instanceof LogEntry
            ? $message
            : new LogEntry(sprintf($message, ...$args));

        return $this;
    }

    public function add(mixed $arg1, mixed $arg2): ActionResultInterface
    {
        if (is_string($arg1) && is_string($arg2)) {
            if (!isset($this->variables[$arg1])) {
                $this->log($arg1 . ' not set');
                return new ExitAction(static::STATUS_GENERAL_ERROR);
            }
            if (!isset($this->variables[$arg2])) {
                $this->log($arg2 . ' not set');
                return new ExitAction(static::STATUS_GENERAL_ERROR);
            }

            $this->variables[$arg1] = $this->variables[$arg1] + $this->variables[$arg2];

            return new ActionResult(static::STATUS_OKAY);
        }

        if (is_string($arg1)) {
            if (!isset($this->variables[$arg1])) {
                $this->log($arg1 . ' not set');
                return new ExitAction(static::STATUS_GENERAL_ERROR);
            }

            $this->variables[$arg1] = $this->variables[$arg1] + $arg2;

            return new ActionResult(static::STATUS_OKAY);
        }

        if (is_string($arg2)) {
            if (!isset($this->variables[$arg2])) {
                $this->log($arg2 . ' not set');
                return new ExitAction(static::STATUS_GENERAL_ERROR);
            }

            $this->variables[$arg2] = $arg1 + $this->variables[$arg2];

            return new ActionResult(static::STATUS_OKAY);
        }

        $this->log($arg1 + $arg2);

        return new ActionResult(static::STATUS_OKAY);
    }

    public function sub(mixed $arg1, mixed $arg2): ActionResultInterface
    {
        if (is_string($arg1) && is_string($arg2)) {
            if (!isset($this->variables[$arg1])) {
                $this->log($arg1 . ' not set');
                return new ExitAction(static::STATUS_GENERAL_ERROR);
            }
            if (!isset($this->variables[$arg2])) {
                $this->log($arg2 . ' not set');
                return new ExitAction(static::STATUS_GENERAL_ERROR);
            }

            $this->variables[$arg1] = $this->variables[$arg1] - $this->variables[$arg2];

            return new ActionResult(static::STATUS_OKAY);
        }

        if (is_string($arg1)) {
            if (!isset($this->variables[$arg1])) {
                $this->log($arg1 . ' not set');
                return new ExitAction(static::STATUS_GENERAL_ERROR);
            }

            $this->variables[$arg1] = $this->variables[$arg1] - $arg2;

            return new ActionResult(static::STATUS_OKAY);
        }

        if (is_string($arg2)) {
            if (!isset($this->variables[$arg2])) {
                $this->log($arg2 . ' not set');
                return new ExitAction(static::STATUS_GENERAL_ERROR);
            }

            $this->variables[$arg2] = $arg1 - $this->variables[$arg2];

            return new ActionResult(static::STATUS_OKAY);
        }

        $this->log($arg1 - $arg2);

        return new ActionResult(static::STATUS_OKAY);
    }

    public function set(string $name, array $value): ActionResultInterface
    {
        if (empty($value)) {
            $this->variables[$name] = null;
            return new ActionResult(static::STATUS_GENERAL_ERROR);
        }

        $result = $this->execute($value);

        // Map into the config that the executed commands were conditions
        array_walk($result, fn (&$item) => $item['condition'] = true);

        $this->variables[$name] = $result[0]['result']->getValue();

        return new CompoundActionResult($result[0]['result']->getStatus(), $result[0]['result']->getValue(), $result);
    }

    public function nav(string $url): ActionResultInterface
    {
        $this->scroll(0, 0);

        $isUrl = filter_var($url, FILTER_VALIDATE_URL);

        if (!$isUrl && !str_starts_with($url, '/')) {
            return new ActionResult(static::STATUS_GENERAL_ERROR, $url . ' is not a valid URL');
        }

        if (!$isUrl) {
            $parts = parse_url($this->webDriver->getCurrentURL());
            $url = $parts['scheme'] . '://' . $parts['host'] . $url;
        }

        $this->webDriver->get($url);

        return new ActionResult(static::STATUS_OKAY);
    }

    public function refresh(): ActionResultInterface
    {
        $this->scroll(0, 0);
        return $this->exec('window.location.reload()');
    }

    public function click(string $selector, bool $allowJsFallback): ActionResultInterface
    {
        try {
            $elements = $this->webDriver->findElements(WebDriverBy::cssSelector($selector));
        } catch (\Throwable $e) {
            if (!$allowJsFallback) {
                return new ActionResult(static::STATUS_GENERAL_ERROR, $this->errorMessage($e));
            }
            return $this->fallbackJsClick($selector);
        }

        if (count($elements) < 1) {
            return new ActionResult(
                static::STATUS_GENERAL_ERROR,
                'Element could not be found with selector: ' . $selector
            );
        }

        try {
            $elements[0]->click();
        } catch (\Throwable $e) {
            if (!$allowJsFallback) {
                return new ActionResult(static::STATUS_GENERAL_ERROR, $this->errorMessage($e));
            }
            return $this->fallbackJsClick($selector);
        }

        return new ActionResult(static::STATUS_OKAY);
    }

    private function fallbackJsClick($selector): ActionResultInterface
    {
        try {
            return $this->exec('
                try {
                    return document.querySelector("' . $selector . '").dispatchEvent(new Event("click"));
                } catch (e) {
                    try {
                        document.querySelector("' . $selector . '").click();
                        return true;
                    } catch (e) {}
                }
                return false;
            ');
        } catch (\Throwable $e) {
            return new ActionResult(static::STATUS_GENERAL_ERROR, $this->errorMessage($e));
        }
    }

    public function moveMouse(string $selector): ActionResultInterface
    {
        try {
            $elements = $this->webDriver->findElements(WebDriverBy::cssSelector($selector));
        } catch (\Throwable $e) {
            return new ActionResult(static::STATUS_GENERAL_ERROR, $this->errorMessage($e));
        }

        if (count($elements) < 1) {
            return new ActionResult(static::STATUS_GENERAL_ERROR, 'Could not find element');
        }

        try {
            $this->webDriver->getMouse()->mouseMove($elements[0]->getCoordinates());
        } catch (\Throwable $e) {
            return new ActionResult(static::STATUS_GENERAL_ERROR, $this->errorMessage($e));
        }

        return new ActionResult(static::STATUS_OKAY);
    }

    public function writeLog(string $message): ActionResultInterface
    {
        $this->log($message);

        return new ActionResult(static::STATUS_OKAY);
    }

    public function screenshot(string $label): ActionResultInterface
    {
        $stub = sprintf(
            'app%shugo%sactions%s%s',
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $this->scriptId ?? 'lostAndFound'
        );

        if (!file_exists(storage_path($stub))) {
            mkdir(storage_path($stub), 0755, true);
        }

        $filename = preg_replace('/[^a-zA-Z1-9\.]/', '_', strtolower($label));

        $i = 0;
        while (file_exists(storage_path($stub . DIRECTORY_SEPARATOR . $filename . '_' . $i . '.png'))) {
            $i++;
        }

        $filename = $stub . DIRECTORY_SEPARATOR . $filename . '_' . $i . '.png';

        $this->webDriver->takeScreenshot(storage_path($filename));

        $screenshot = new Screenshot($filename, $label);
        $this->log($screenshot);

        return new ActionResult(static::STATUS_OKAY, $screenshot);
    }

    public function scroll(int $x, int $y): ActionResultInterface
    {
        return $this->exec(sprintf('window.scrollBy(%s, %s)', $x, $y));
    }

    public function scrollTo(string $selector, ?int $offset = 0): ActionResultInterface
    {
        return $this->exec(
            sprintf('window.scrollTo(0, (document.querySelector("%s").offsetTop - %s))', $selector, $offset ?? 0)
        );
    }

    public function sendKeys(string $keys): ActionResultInterface
    {
        $this->log('sending keys `%s`', $keys);

        try {
            $result = $this->webDriver->getKeyboard()->sendKeys($keys);
            return new ActionResult($result ? static::STATUS_OKAY : static::STATUS_GENERAL_ERROR, $result);
        } catch (\Throwable $e) {
            return $this->exec(sprintf('
                if (typeof document.activeElement === "undefined") {
                    return false;
                }
                document.activeElement.dispatchEvent(new Event("keydown"));
                document.activeElement.value = `%s`;
                document.activeElement.dispatchEvent(new Event("keyup"));
                document.activeElement.dispatchEvent(new Event("change"));
                document.activeElement.dispatchEvent(new Event("input"));
                return document.activeElement.value;
            ', $keys));
        }
    }

    public function wait(int $seconds): ActionResultInterface
    {
        $this->log('waiting for %d seconds', $seconds);
        sleep($seconds);
        return new ActionResult(static::STATUS_OKAY);
    }

    public function uwait(int $microseconds): ActionResultInterface
    {
        $this->log('waiting for %d microseconds', $microseconds);
        usleep($microseconds);
        return new ActionResult(static::STATUS_OKAY);
    }

    public function waitFor(string $selector, int $timeout = 5): ActionResultInterface
    {
        $this->log('waiting for %s element', $selector);

        try {
            $start = microtime(true);

            $this->webDriver->wait($timeout)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector($selector))
            );

            return new ActionResult(static::STATUS_OKAY, 'took ' . microtime(true) - $start . ' seconds');
        } catch (\Throwable $e) {
            return new ActionResult(static::STATUS_GENERAL_ERROR, $e->getMessage());
        }
    }

    public function ifStatement(array $condition, bool $invert, array $then, array $else): ActionResultInterface
    {
        $this->log(new CommandResult('ifStatement', $condition));

        $test = $this->execute($condition);

        // Map into the config that the executed commands were conditions
        array_walk($test, fn (&$item) => $item['condition'] = true);

        $result = $test[0]['result']->successful();

        if (($result && !$invert) || (!$result && $invert)) {
            return new CompoundActionResult(static::STATUS_OKAY, 'then', [...$test, ...$this->execute($then)]);
        }

        if (($result && $invert) || (!$result && !$invert)) {
            return new CompoundActionResult(static::STATUS_GENERAL_ERROR, 'else', [...$test, ...$this->execute($else)]);
        }

        return new ActionResult(static::STATUS_UNCAUGHT_ERROR);
    }

    public function exec(string $code): ActionResultInterface
    {
        $result = $this->webDriver->executeScript($code);

        return new ActionResult(
            !is_null($result)
                ? ($result ? static::STATUS_OKAY : static::STATUS_GENERAL_ERROR)
                : static::STATUS_OKAY,
            $result
        );
    }

    public function exit(int $status): ExitAction
    {
        return new ExitAction($status);
    }

    public function getInputValue(string $selector): ActionResultInterface
    {
        return $this->exec(sprintf('return document.querySelector("%s").value', $selector));
    }

    public function setInputValue(string $selector, string $value): ActionResultInterface
    {
        return $this->exec(sprintf('
            var setInputValueEl = document.querySelector("%s");
            setInputValueEl.dispatchEvent(new Event("keydown"));
            setInputValueEl.value = "%s";
            setInputValueEl.dispatchEvent(new Event("keyup"));
            setInputValueEl.dispatchEvent(new Event("change"));
            setInputValueEl.dispatchEvent(new Event("input"));
        ', $selector, $value));
    }

    public function getBrowserLogs(string $report): ActionResultInterface
    {
        $logs = $this->webDriver->manage()->getLog('browser');

        $this->log(new LogEntry(json_encode($logs, JSON_PRETTY_PRINT)));

        $status = static::STATUS_OKAY;
        foreach ($logs as $log) {
            if ($log['level'] === 'SEVERE' && ($report === 'all' || $log['source'] === $report)) {
                $status = static::STATUS_GENERAL_ERROR;
                break;
            }
        }

        return new ActionResult($status, $logs);
    }

    public function deleteCookies(): ActionResultInterface
    {
        $this->webDriver->manage()->deleteAllCookies();
        return new ActionResult(static::STATUS_OKAY);
    }

    public function noop(): ActionResultInterface
    {
        return new ActionResult(static::STATUS_OKAY);
    }

    public function visible(string $selector): ActionResultInterface
    {
        return $this->exec(sprintf('
            var elem = document.querySelector(`%s`);
            return elem ? !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length) : false;
        ', $selector));
    }

    public function echo(string $value): ActionResultInterface
    {
        if (str_starts_with($value, '$') && isset($this->variables[substr($value, 1)])) {
            $value = $this->variables[substr($value, 1)];
        }

        return new ActionResult(static::STATUS_OKAY, $value);
    }

    public function elementText(string $selector): ActionResultInterface
    {
        try {
            return new ActionResult(
                static::STATUS_OKAY,
                $this->webDriver->findElement(WebDriverBy::cssSelector($selector))->getText()
            );
        } catch (\Throwable $e) {
            $this->log('unable to get text, with error: `%s`. Retrying with js...', $e->getMessage());
            return $this->exec(sprintf('return document.querySelector("%s").textContent;', $selector));
        }
    }

    public function equals(array $arg1, array $arg2): ActionResultInterface
    {
        $this->log(new CommandResult('equals', ['arg1' => $arg1, 'arg2' => $arg2]));

        $result1 = $this->execute($arg1)[0]['result'] ?? new ActionResult(static::STATUS_GENERAL_ERROR, '$arg1 failed');
        $result2 = $this->execute($arg2)[0]['result'] ?? new ActionResult(static::STATUS_GENERAL_ERROR, '$arg2 failed');

        if ($result1->value && $result2->value && $result1->value == $result2->value) {
            return new ActionResult(static::STATUS_OKAY, $result1->value);
        }

        if ($result1->status && $result2->status && $result1->status == $result2->status) {
            return new ActionResult(static::STATUS_OKAY, $result1->status);
        }

        return new ActionResult(static::STATUS_GENERAL_ERROR, 'values not equal');
    }

}
