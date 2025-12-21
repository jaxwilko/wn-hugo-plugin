<?php

namespace JaxWilko\Hugo\Classes\Chrome;

use Winter\Storm\Exception\ApplicationException;

class Chrome
{
    public const string CHROME_INSTALL_PATH = 'hugo/google-chrome/%s';

    public static function path(string $path = ''): string
    {
        if (!env('HUGO_CHROME_VERSION')) {
            throw new ApplicationException('Please run `hugo:install-chrome`');
        }

        $path = realpath(storage_path(
            sprintf(static::CHROME_INSTALL_PATH, env('HUGO_CHROME_VERSION')) . ($path ? '/' . ltrim($path, '/') : '')
        ));

        if (!$path || !file_exists($path)) {
            throw new ApplicationException('Could not find Chrome version');
        }

        return $path;
    }

    public static function configureEnvPath(): void
    {
        $path = static::path();

        // Prepend the path with our chrome install
        $envPath = implode(PATH_SEPARATOR, [
            $path . '/chrome-linux64',
            $path . '/chromedriver-linux64',
            getenv('PATH')
        ]);

        // Push it into the env for subprocesses
        putenv('PATH=' . $envPath);
        // Also append it here because Facebook uses it...
        $_ENV['PATH'] = $envPath;

        // Force the chromedriver to be used
        putenv('WEBDRIVER_CHROME_DRIVER=' . $path . '/chromedriver-linux64/chromedriver');
    }
}
