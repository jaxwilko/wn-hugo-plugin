<?php

namespace JaxWilko\Hugo\Classes\Lighthouse;

use JaxWilko\Hugo\Models\LighthouseUrl;
use Symfony\Component\Process\Process;

class Lighthouse
{
    const PROCESS_TIMEOUT = 360;
    const PROCESS_OPTIONS = [
        '--only-categories performance',
        '--quiet',
        '--chrome-flags="--headless --no-sandbox --disable-dev-shm-usage"',
        '--disable-storage-reset',
        '--output json',
    ];

    public static function report(LighthouseUrl $url): LighthouseReportProcessor
    {
        $target = $url->makeTarget();

        if (!filter_var($target, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('invalid url passed to lighthouse test manager');
        }

        $process = Process::fromShellCommandline(implode(
            ' ',
            array_merge([static::lighthouseBinary(), $target], static::PROCESS_OPTIONS)
        ));

        $process->setWorkingDirectory(base_path())->setTimeout(static::PROCESS_TIMEOUT)->run();

        if ($process->getExitCode() > 0) {
            throw new \RuntimeException('Lighthouse failed: ' . PHP_EOL . $process->getErrorOutput());
        }

        $report = json_decode($process->getOutput());

        if (!$report || json_last_error()) {
            throw new \RuntimeException('Report is not valid');
        }

        return new LighthouseReportProcessor($url, $report);
    }

    protected static function lighthouseBinary(): string
    {
        return base_path('node_modules/.bin/lighthouse');
    }
}
