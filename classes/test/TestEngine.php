<?php

namespace JaxWilko\Hugo\Classes\Test;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use JaxWilko\Hugo\Classes\Test\Actions\CommandResult;
use JaxWilko\Hugo\Classes\Test\Actions\Contracts\ActionInterface;
use JaxWilko\Hugo\Classes\Test\Actions\ActionResult;
use JaxWilko\Hugo\Classes\Test\Actions\Contracts\LogItemInterface;
use JaxWilko\Hugo\Classes\Test\Actions\ExitAction;
use JaxWilko\Hugo\Classes\Test\Actions\LogEntry;
use JaxWilko\Hugo\Classes\Test\Actions\Screenshot;

class TestEngine
{
    public const STATUS_OKAY = 0;
    public const STATUS_GENERAL_ERROR = 1;
    public const STATUS_UNCAUGHT_ERROR = 2;
    public const STATUS_NO_EXIT_ERROR = 3;

    protected array $variables = [];

    protected array $log = [];

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

    public function run(string $url, array $config): static
    {
        // Run the test but first navigate to the page
        $this->execute(array_prepend($config, [
            'url' => $url,
            '_group' => 'nav'
        ]));

        return $this;
    }

    public function execute(array $config): array
    {
        foreach ($config as $index => $action) {
            $method = $action['_group'];
            unset($action['_group']);

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
        if ($message instanceof Screenshot || $message instanceof CommandResult) {
            $this->log[] = $message;
            return $this;
        }

        $this->log[] = new LogEntry(sprintf($message, ...$args));
        return $this;
    }

    public function add(mixed $arg1, mixed $arg2): ActionInterface
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

    public function sub(mixed $arg1, mixed $arg2): ActionInterface
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

    public function set(string $name, mixed $value): ActionInterface
    {
        $this->variables[$name] = $value;

        return new ActionResult(1);
    }

    public function nav(string $url): ActionInterface
    {
        $this->scroll(0, 0);
        $this->webDriver->get($url);

        return new ActionResult(static::STATUS_OKAY);
    }

    public function refresh(): ActionInterface
    {
        $this->scroll(0, 0);
        return $this->exec('window.location.reload()');
    }

    public function click(string $selector, bool $allowJsFallback): ActionInterface
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

    private function fallbackJsClick($selector): ActionInterface
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

    public function moveMouse(string $selector): ActionInterface
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

    public function writeLog(string $message): void
    {
        $this->log($message);
    }

    public function screenshot(string $label): ActionInterface
    {
        $stub = sprintf(
            'app%shugo%stests%s%s',
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

        $this->log(new Screenshot($filename, $label));

        return new ActionResult(static::STATUS_OKAY);
    }

    public function scroll(int $x, int $y): ActionInterface
    {
        return $this->exec(sprintf('window.scrollBy(%s, %s)', $x, $y));
    }

    public function scrollTo(string $selector, ?int $offset = 0): ActionInterface
    {
        return $this->exec(
            sprintf('window.scrollTo(0, (document.querySelector("%s").offsetTop - %s))', $selector, $offset ?? 0)
        );
    }

    public function sendKeys(string $keys): ActionInterface
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

    public function wait(int $seconds): ActionInterface
    {
        $this->log('waiting for %d seconds', $seconds);
        sleep($seconds);
        return new ActionResult(static::STATUS_OKAY);
    }

    public function uwait(int $microseconds): ActionInterface
    {
        $this->log('waiting for %d microseconds', $microseconds);
        usleep($microseconds);
        return new ActionResult(static::STATUS_OKAY);
    }

    public function waitFor(string $selector, int $timeout = 5): ActionInterface
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

    public function ifStatement(array $condition, bool $invert, array $then, array $else): void
    {
        $this->log(new CommandResult('ifStatement', $condition));

        $test = $this->execute($condition)[0]['result']->successful();

        if (($test && !$invert) || (!$test && $invert)) {
            $this->execute($then);
        }

        if (($test && $invert) || (!$test && !$invert)) {
            $this->execute($else);
        }
    }

    public function exec(string $code): ActionInterface
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

    public function getInputValue(string $selector): ActionInterface
    {
        return $this->exec(sprintf('return document.querySelector("%s").value', $selector));
    }

    public function setInputValue(string $selector, string $value): ActionInterface
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

    public function dumpConsole(): ActionInterface
    {
        $logs = $this->webDriver->manage()->getLog('browser');
        $this->log(json_encode($logs, JSON_PRETTY_PRINT));
        return new ActionResult(static::STATUS_OKAY, $logs);
    }

    public function deleteCookies(): ActionInterface
    {
        $this->webDriver->manage()->deleteAllCookies();
        return new ActionResult(static::STATUS_OKAY);
    }

    public function visible(string $selector): ActionInterface
    {
        return $this->exec(sprintf('
            var elem = document.querySelector("%s");
            return elem ? !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length) : false;
        ', $selector));
    }

    public function echo(string $value): ActionInterface
    {
        return new ActionResult(static::STATUS_OKAY, $value);
    }

    public function elementText(string $selector): ActionInterface
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

    public function equals(array $arg1, array $arg2): ActionInterface
    {
        $this->log(new CommandResult('equals', ['arg1' => $arg1, 'arg2' => $arg2]));

        $result1 = $this->execute($arg1)[0]['result'] ?? new ActionResult(static::STATUS_GENERAL_ERROR, '$arg1 failed');
        $result2 = $this->execute($arg2)[0]['result'] ?? new ActionResult(static::STATUS_GENERAL_ERROR, '$arg2 failed');

        foreach ([$result1, $result2] as $result) {
            if ($result instanceof ActionResult) {
                return $result;
            }
        }

        if ($result1->value && $result2->value && $result1->value == $result2->value) {
            return new ActionResult(static::STATUS_OKAY, $result1->value);
        }

        if ($result1->status && $result2->status && $result1->status == $result2->status) {
            return new ActionResult(static::STATUS_OKAY, $result1->status);
        }

        return new ActionResult(static::STATUS_GENERAL_ERROR, 'values not equal');
    }

}
