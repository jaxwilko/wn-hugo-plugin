<?php

namespace JaxWilko\Hugo\Models;

use Carbon\Carbon;
use DB;
use Model;
use Winter\Storm\Database\Builder;
use Winter\Storm\Database\Relations\HasMany;

/**
 * LighthouseUrl Model
 */
class LighthouseUrl extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_lighthouse_urls';

    protected bool $hasReports;

    protected ?array $chartDataCache = null;

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'reports' => [
            \JaxWilko\Hugo\Models\LighthouseReport::class,
            'key' => 'url_id',
            'otherKey' => 'id'
        ]
    ];

    public $belongsTo = [
        'site' => [
            \JaxWilko\Hugo\Models\Site::class,
        ]
    ];

    public function makeTarget(): string
    {
        return $this->site->base_url . $this->url;
    }

    protected function getAveragesBuilder(): HasMany
    {
        return $this->reports()
            ->select(DB::raw('
                AVG(performance) as `score`,
                AVG(fcp_score) as `fcp`,
                AVG(lcp_score) as `lcp`,
                AVG(fmp_score) as `fmp`,
                AVG(cls_score) as `cls`,
                AVG(si_score)  as `si`,
                AVG(srt_score) as `srt`,
                AVG(fid_score) as `fid`,
                AVG(int_score) as `int`,
                AVG(ucr_score) as `ucr`,
                AVG(ujc_score) as `ujc`,
                AVG(ds_score) as `ds`,
                COUNT(*) as `count`
            '));
    }

    protected function hasReports(): bool
    {
        return $this->hasReports = $this->reports()->count() > 0;
    }

    public function getAverages(): array
    {
        if (!$this->hasReports()) {
            return [];
        }

        return $this->getAveragesBuilder()
            ->first()
            ->toArray();
    }

    public function getSevenDayAverages(): array
    {
        if (!$this->hasReports()) {
            return [];
        }

        return $this->getAveragesBuilder()
            ->where('created_at', '>', Carbon::now()->subWeek()->format('Y-m-d'))
            ->first()
            ->toArray();
    }

    public function getChartData(): array
    {
        if ($this->chartDataCache) {
            return $this->chartDataCache;
        }

        $data = [
            'performance' => ['colour' => '#51BBFE', 'data' => []],
            'fcp' => ['colour' => '#EF7B45', 'data' => []],
            'lcp' => ['colour' => '#D84727', 'data' => []],
            'fmp' => ['colour' => '#F7FE72', 'data' => []],
            'cls' => ['colour' => '#8FF7A7', 'data' => []],
            'si' => ['colour' => '#603140', 'data' => []],
            'srt' => ['colour' => '#D3D57C', 'data' => []],
            'fid' => ['colour' => '#E4E6C3', 'data' => []],
            'int' => ['colour' => '#899878', 'data' => []],
            'ucr' => ['colour' => '#5EB1BF', 'data' => []],
            'ujc' => ['colour' => '#567568', 'data' => []],
            'ds' => ['colour' => '#FC6471', 'data' => []],
        ];

        foreach ($this->reports()->orderBy('created_at')->get() as $report) {
            foreach ($data as $key => $d) {
                if ($key === 'performance') {
                    $data[$key]['data'][] = [$report->created_at->timestamp * 1000, $report->performance * 100];
                    continue;
                }
                $data[$key]['data'][] = [$report->created_at->timestamp * 1000, $report->{$key . '_score'} * 100];
            }
        }

        return $this->chartDataCache = $data;
    }
}
