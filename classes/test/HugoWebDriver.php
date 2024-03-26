<?php

namespace JaxWilko\Hugo\Classes\Test;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverKeyboard;
use Facebook\WebDriver\WebDriverNavigationInterface;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverTargetLocator;
use Facebook\WebDriver\WebDriverWait;
use Illuminate\Support\Facades\Log;
use Winter\Storm\Exception\ApplicationException;

/**
 * @method WebDriver close()
 * @method WebDriver get(string $url)
 * @method string getCurrentURL()
 * @method string getPageSource()
 * @method string getTitle()
 * @method string getWindowHandle()
 * @method array getWindowHandles()
 * @method WebDriverKeyboard getKeyboard()
 * @method void quit()
 * @method string takeScreenshot(string $save_as = null)
 * @method WebDriverWait wait(int $timeout_in_second = 30, int $interval_in_millisecond = 250)
 * @method WebDriverOptions manage()
 * @method WebDriverNavigationInterface navigate(string $url)
 * @method WebDriverTargetLocator switchTo()
 * @method mixed execute(string $name, array $params)
 * @method mixed executeScript(string $script)
 *
 * @see \Facebook\WebDriver\WebDriver
 */

class HugoWebDriver
{
    public function __construct(protected ?WebDriver $webDriver)
    {
        $this->webDriver->manage()->window()->setSize(new WebDriverDimension(1920, 934));
    }

    public static function make(): static
    {
        if (!env('webdriver.chrome.driver')) {
            if (!env('HUGO_WEB_DRIVER')) {
                throw new ApplicationException('Please set a `HUGO_WEB_DRIVER` env');
            }
            putenv(sprintf('webdriver.chrome.driver=%s', env('HUGO_WEB_DRIVER')));
        }

        $options = ['--headless', '--start-maximized', '--kiosk', '--no-sandbox', '--disable-dev-shm-usage'];

        if (isset($browserConfig->userAgent)) {
            $options[] = '--user-agent=' . $browserConfig->userAgent;
        }

        return new static(
            ChromeDriver::start(
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY,
                    (new ChromeOptions())->addArguments($options)
                )->setCapability(
                    'loggingPrefs',
                    ['browser' => 'ALL']
                )
            )
        );
    }

    public function kill(): void
    {
        $this->webDriver->quit();
        $this->webDriver = null;
    }

    public function running(): bool
    {
        return !is_null($this->webDriver);
    }

    public function __call(string $name, array $args): mixed
    {
        if (is_null($this->webDriver)) {
            throw new \RuntimeException('Webdriver not running');
        }

        try {
            return $this->webDriver->{$name}(...$args);
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), $e->getTrace());
            throw new ApplicationException($e->getMessage());
        }
    }
}
