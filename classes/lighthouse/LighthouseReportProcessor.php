<?php

namespace JaxWilko\Hugo\Classes\Lighthouse;

use JaxWilko\Hugo\Models\LighthouseUrl;
use JaxWilko\Hugo\Models\LighthouseReport;

/**
 * @class LighthouseReport
 *
 * @method array getFirstContentfulPaint
 * @method array getLargestContentfulPaint
 * @method array getFirstMeaningfulPaint
 * @method array getSpeedIndex
 * @method array getCumulativeLayoutShift
 * @method array getServerResponseTime
 * @method array getMaxPotentialFid
 * @method array getInteractive
 * @method array getNetworkServerLatency
 * @method array getUnusedCssRules
 * @method array getUnusedJavascript
 * @method array getDomSize
 */
class LighthouseReportProcessor
{
    protected LighthouseUrl $url;

    protected float $performance = 0.0;

    protected array $keyMap = [
        'fcp' => 'first-contentful-paint',
        'lcp' => 'largest-contentful-paint',
        'fmp' => 'first-meaningful-paint',
        'si'  => 'speed-index',
        'cls' => 'cumulative-layout-shift',
        'srt' => 'server-response-time',
        'fid' => 'max-potential-fid',
        'int' => 'interactive',
        'nsl' => 'network-server-latency',
        'ucr' => 'unused-css-rules',
        'ujc' => 'unused-javascript',
        'ds'  => 'dom-size'
    ];

    protected array $results = [
        'first-contentful-paint',
        'largest-contentful-paint',
        'first-meaningful-paint',
        'speed-index',
        'cumulative-layout-shift',
        'server-response-time',
        'max-potential-fid',
        'interactive',
        'network-server-latency',
        'unused-css-rules',
        'unused-javascript',
        'dom-size'
    ];

    protected array $images = [
        'finished' => '',
        'timeline' => []
    ];

    public function __construct(LighthouseUrl $url, object $report)
    {
        $this->url = $url;

        $this->performance = $report->categories->performance->score ?? 0;
        $this->images['finished'] = $this->prepareImage($report->audits->{'final-screenshot'}->details->data);

        foreach ($this->results as $result) {
            $this->results[$result] = [
                'score'     => $report->audits->{$result}->score ?? null,
                'value'     => $report->audits->{$result}->numericValue ?? null,
                'unit'      => $report->audits->{$result}->numericUnit ?? null,
                'display'   => $report->audits->{$result}->displayValue ?? null,
            ];
        }

        foreach ($report->audits->{'screenshot-thumbnails'}->details->items as $thumbnail) {
            $this->images['timeline'][$thumbnail->timing] = $this->prepareImage($thumbnail->data);
        }
    }

    public function __call(string $name, array $args = []): mixed
    {
        if (substr($name, 0, 3) !== 'get') {
            throw new \BadMethodCallException('Method ' . $name . ' not found');
        }

        $key = strtolower(preg_replace('/\B([A-Z])/', '-$1', substr($name, 3)));

        if (!isset($this->results[$key])) {
            throw new \BadMethodCallException('No result key found for ' . $key);
        }

        return $this->results[$key];
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getPerformance(): float
    {
        return $this->performance;
    }

    public function getImage(): string
    {
        return $this->images['finished'];
    }

    public function getTimeline(): array
    {
        return $this->images['timeline'];
    }

    protected function prepareImage(string $image): string
    {
        return base64_decode(str_replace('data:image/jpeg;base64,', '', $image));
    }

    public function save(): LighthouseReport
    {
        $data = [
            'url_id' => $this->url->id,
            'performance' => $this->getPerformance()
        ];

        foreach (['fcp', 'lcp', 'fmp', 'cls', 'si', 'srt', 'fid', 'int', 'nsl', 'ucr', 'ujc', 'ds'] as $key) {
            foreach (['score', 'value', 'unit', 'display'] as $item) {
                $data[$key . '_' . $item] = $this->results[$this->keyMap[$key]][$item];
            }
        }

        $report = $this->url->reports()->save(new LighthouseReport($data));

        $dir = storage_path('app/lighthouse/' . $report->id);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            mkdir($dir . '/timeline', 0755);
        }

        file_put_contents($dir . '/finished.jpg', $this->getImage());

        foreach ($this->getTimeline() as $name => $image) {
            file_put_contents(sprintf('%s/timeline/%s.jpg', $dir, $name), $image);
        }

        return $report;
    }
}
