<?php

namespace JaxWilko\Hugo\Classes\Lighthouse;

use Illuminate\Support\Facades\File;
use JaxWilko\Hugo\Classes\Chrome\Chrome;
use JaxWilko\Hugo\Models\LighthouseReport;
use JaxWilko\Hugo\Models\SiteUrl;
use Symfony\Component\Process\Process;
use System\Models\EventLog;
use Throwable;

class Lighthouse
{
    public const int LH_WINDOW_X = 500;
    public const int LH_WINDOW_Y = 960;
    public const int PROCESS_TIMEOUT = 360;

    protected SiteUrl $siteUrl;
    protected array|Throwable $report;

    public function __construct(SiteUrl $siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    public static function make(SiteUrl $url): static
    {
        return new static($url);
    }

    public function generateReport(): static
    {
        $target = $this->siteUrl->target;

        if (!filter_var($target, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('invalid url passed to lighthouse test manager');
        }

        try {

            $process = Process::fromShellCommandline(implode(' ', [
                'CHROME_PATH=' . Chrome::path('chrome-linux64/chrome'),
                $this->lighthouseBinary(),
                $target,
                ...$this->getLighthouseFlags()
            ]));

            $process->setWorkingDirectory(base_path())
                ->setTimeout(static::PROCESS_TIMEOUT)
                ->run();

            if ($process->getExitCode() > 0) {
                $output = $process->getOutput();
                preg_match('/"runtimeError"\s*:\s*(\{[\s\S]*?\n\s*\})/', $output, $matches);
                $error = json_decode($matches[1], JSON_OBJECT_AS_ARRAY) ?? [];
                $error['title'] = $process->getErrorOutput();
                throw new \RuntimeException(json_encode($error));
            }

            $report = json_decode($process->getOutput(), JSON_OBJECT_AS_ARRAY);

            if (!$report || json_last_error()) {
                throw new \RuntimeException('Report is not valid');
            }

            $this->report = $report;
        } catch (\Throwable $e) {
            $this->report = $e;
        }

        return $this;
    }

    protected function lighthouseBinary(): string
    {
        return base_path('node_modules/.bin/lighthouse');
    }

    protected function getLighthouseFlags(): array
    {
        return [
            '--only-categories accessibility,best-practices,performance,seo',
            '--quiet',
            '--screenEmulation.mobile',
            '--screenEmulation.width=' . static::LH_WINDOW_X,
            '--screenEmulation.height=' . static::LH_WINDOW_Y,
            '--screenEmulation.deviceScaleFactor=2',
            '--chrome-flags="' . implode(' ', [
                '--headless',
                '--no-sandbox',
                '--start-maximized',
                '--disable-dev-shm-usage',
                '--window-size=' . static::LH_WINDOW_X . ',' . static::LH_WINDOW_Y,
            ]) . '"',
            '--no-enable-error-reporting',
            '--disable-storage-reset',
            '--output json',
        ];
    }

    public function save(): LighthouseReport
    {
        if ($this->report instanceof Throwable) {
            return $this->siteUrl->reports()->save(new LighthouseReport([
                'score_performance' => 0,
                'score_accessibility' => 0,
                'score_best_practice' => 0,
                'score_seo' => 0,
                'performance_first_contentful_paint' => 0,
                'performance_largest_contentful_paint' => 0,
                'performance_total_blocking_time' => 0,
                'performance_cumulative_layout_shift' => 0,
                'performance_speed_index' => 0,
                'report' => (new EventLog())->getDetails($this->report),
            ]));
        }

        $report = $this->siteUrl->reports()->save(new LighthouseReport([
            'score_performance' => $this->report['categories']['performance']['score'],
            'score_accessibility' => $this->report['categories']['accessibility']['score'],
            'score_best_practice' => $this->report['categories']['best-practices']['score'],
            'score_seo' => $this->report['categories']['seo']['score'],
            'performance_first_contentful_paint' => $this->report['audits']['first-contentful-paint']['score'],
            'performance_largest_contentful_paint' => $this->report['audits']['largest-contentful-paint']['score'],
            'performance_total_blocking_time' => $this->report['audits']['total-blocking-time']['score'],
            'performance_cumulative_layout_shift' => $this->report['audits']['cumulative-layout-shift']['score'],
            'performance_speed_index' => $this->report['audits']['speed-index']['score'],
            'report' => $this->trimReport($this->report),
        ]));

        $reportAssetDir = storage_path('app/hugo/lighthouse/' . $report->id);

        if (!File::isDirectory($reportAssetDir)) {
            File::makeDirectory($reportAssetDir . '/timeline', 0755, true);
        }

        $fullImage = $this->prepareImage($this->report['fullPageScreenshot']['screenshot']['data']);
        File::put(
            $reportAssetDir . '/full-page.' . $fullImage['type'],
            $fullImage['data']
        );

        $finalImage = $this->prepareImage($this->report['audits']['final-screenshot']['details']['data']);
        File::put(
            $reportAssetDir . '/final.' . $finalImage['type'],
            $finalImage['data']
        );

        foreach ($this->report['audits']['screenshot-thumbnails']['details']['items'] as $index => $thumbnail) {
            $image = $this->prepareImage($thumbnail['data']);
            File::put(
                sprintf('%s/timeline/%s.%s', $reportAssetDir, $thumbnail['timing'], $image['type']),
                $image['data']
            );
        }

        return $report;
    }

    protected function trimReport(array $report): array
    {
        unset(
            $report['configSettings'],
            $report['categoryGroups'],
            $report['stackPacks'],
            $report['entities'],
            $report['fullPageScreenshot'],
            $report['timing'],
            $report['i18n'],
            $report['audits']['screenshot-thumbnails'],
            $report['audits']['final-screenshot'],
        );

        return $report;
    }

    protected function prepareImage(string $image): array
    {
        $parts = explode('base64,', $image, 2);
        return [
            'type' => match ($parts[0]) {
                'data:image/jpeg;' => 'jpg',
                'data:image/webp;' => 'webp',
            },
            'data' => base64_decode($parts[1]),
        ];
    }
}
