<?php

namespace JaxWilko\Hugo\Classes\Script;

use Facebook\WebDriver\WebDriverBy;
use JaxWilko\Hugo\Classes\Script\ActionResult;

class ScriptEngine
{
    protected array $variables = [];

    public function __construct(
        protected HugoWebDriver $webDriver,
        protected string $scriptId
    ) {}

    public static function init(HugoWebDriver $webDriver): static
    {
        return new static($webDriver, str_replace('.', '', (string) microtime(true)));
    }

    public function run(string $url, array $config): array
    {
        return $this->execute(array_prepend($config, [
            'url' => $url,
            '_group' => 'nav'
        ]));
    }

    public function execute(array $config): array
    {
        foreach ($config as $index => $action) {
            $method = $action['_group'];
            unset($action['_group']);
            $config[$index]['result'] = $this->{$method}(...$action);
        }

        return $config;
    }

    public function add(mixed $arg1, mixed $arg2): ActionResult
    {
        if (is_string($arg1) && is_string($arg2)) {
            if (!isset($this->variables[$arg1])) {
                return new ActionResult(false, [$arg1 . ' not set']);
            }
            if (!isset($this->variables[$arg2])) {
                return new ActionResult(false, [$arg2 . ' not set']);
            }
            return new ActionResult($this->variables[$arg1] = $this->variables[$arg1] + $this->variables[$arg2]);
        }

        if (is_string($arg1)) {
            if (!isset($this->variables[$arg1])) {
                return new ActionResult(false, [$arg1 . ' not set']);
            }
            return new ActionResult($this->variables[$arg1] = $this->variables[$arg1] + $arg2);
        }

        if (is_string($arg2)) {
            if (!isset($this->variables[$arg2])) {
                return new ActionResult(false, [$arg2 . ' not set']);
            }
            return new ActionResult($this->variables[$arg2] = $arg1 + $this->variables[$arg2]);
        }

        return new ActionResult($arg1 + $arg2);
    }

    public function sub(mixed $arg1, mixed $arg2): ActionResult
    {
        if (is_string($arg1) && is_string($arg2)) {
            if (!isset($this->variables[$arg1])) {
                return new ActionResult(false, [$arg1 . ' not set']);
            }
            if (!isset($this->variables[$arg2])) {
                return new ActionResult(false, [$arg2 . ' not set']);
            }
            return new ActionResult($this->variables[$arg1] = $this->variables[$arg1] - $this->variables[$arg2]);
        }

        if (is_string($arg1)) {
            if (!isset($this->variables[$arg1])) {
                return new ActionResult(false, [$arg1 . ' not set']);
            }
            return new ActionResult($this->variables[$arg1] = $this->variables[$arg1] - $arg2);
        }

        if (is_string($arg2)) {
            if (!isset($this->variables[$arg2])) {
                return new ActionResult(false, [$arg2 . ' not set']);
            }
            return new ActionResult($this->variables[$arg2] = $arg1 - $this->variables[$arg2]);
        }

        return new ActionResult($arg1 - $arg2);
    }

    public function set(string $name, mixed $value): ActionResult
    {
        $this->variables[$name] = $value;

        return new ActionResult(true);
    }

    public function nav(string $url): ActionResult
    {
        $this->scroll(0, 0);
        $this->webDriver->get($url);

        return new ActionResult(true);
    }

    public function refresh(): ActionResult
    {
        $this->scroll(0, 0);
        return ActionResult::fromResult($this->exec('window.location.reload()'), true);
    }

    public function click(string $selector, bool $allowJsFallback): ActionResult
    {
        try {
            $elements = $this->webDriver->findElements($this->webDriver->cssSelector($selector));
        } catch (\Throwable $e) {
            if (!$allowJsFallback) {
                return new ActionResult(false, [$e->getMessage()]);
            }
            return $this->fallbackJsClick($selector);
        }

        if (count($elements) < 1) {
            return new ActionResult(false, ['Could not find element']);
        }

        try {
            $elements[0]->click();
        } catch (\Throwable $e) {
            if (!$allowJsFallback) {
                return new ActionResult(false, [$e->getMessage()]);
            }
            return $this->fallbackJsClick($selector);
        }

        return new ActionResult(true);
    }

    private function fallbackJsClick($selector): ActionResult
    {
        try {
            return new ActionResult($this->exec('
                try {
                    return document.querySelector("' . $selector . '").dispatchEvent(new Event("click"));
                } catch (e) {
                    try {
                        document.querySelector("' . $selector . '").click();
                        return true;
                    } catch (e) {}
                }
                return false;
            '));
        } catch (\Throwable $e) {
            return new ActionResult(false, [$e->getMessage()]);
        }
    }

    public function moveMouse(string $selector): ActionResult
    {
        try {
            $elements = $this->webDriver->findElements(WebDriverBy::cssSelector($selector));
        } catch (\Throwable $e) {
            return new ActionResult(false, [$e->getMessage()]);
        }

        if (count($elements) < 1) {
            return new ActionResult(false, ['Could not find element']);
        }

        try {
            $this->webDriver->getMouse()->mouseMove($elements[0]->getCoordinates());
        } catch (\Throwable $e) {
            return new ActionResult(false, [$e->getMessage()]);
        }

        return new ActionResult(true);
    }

    public function screenshot(string $label): ActionResult
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

        return new ActionResult($filename, [], [$label => $filename]);
    }

    public function scroll(int $x, int $y): ActionResult
    {
        // @TODO: implement

        return new ActionResult(true);
    }

    public function scrollTo(string $selector, int $offset): ActionResult
    {
        // @TODO: implement

        return new ActionResult(true);
    }

    public function sendKeys(string $keys): ActionResult
    {
        // @TODO: implement

        return new ActionResult(true);
    }

    public function wait(int $seconds): ActionResult
    {
        sleep($seconds);
        return new ActionResult(true);
    }

    public function ifStatement(array $condition, bool $invert, array $then, array $else): ActionResult
    {
        $test = $this->execute($condition);

        if (!$test[0]['result']) {
            return ActionResult::fromResult($test[0]['result'], false);
        }

        if ($test[0]['result']->getResult()) {
            $result = $this->execute($then);
        }

        return new ActionResult(true);
    }

    public function exit(int $status): ActionResult
    {
        // @TODO: implement

        return new ActionResult(true);
    }

    public function exec(string $code): ActionResult
    {
        return new ActionResult($this->webDriver->executeScript($code));
    }
}
