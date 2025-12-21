<?php

namespace JaxWilko\Hugo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Winter\Storm\Database\Model;
use Winter\Storm\Support\Facades\DB;

/**
 * LighthouseUrl Model
 */
class SiteUrl extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_site_urls';

    protected bool $hasReports;

    protected ?array $chartDataCache = null;

    protected $appends = [
        'target',
    ];

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
        ]
    ];

    public $belongsTo = [
        'site' => [
            \JaxWilko\Hugo\Models\Site::class,
        ]
    ];

    public function getTargetAttribute(): string
    {
        return $this->site->base_url . $this->url;
    }

    protected function getAveragesBuilder(): Relation
    {
        return $this->reports()
            ->select(DB::raw('
                ROUND(AVG(score_performance), 2) AS `score_performance`,
                ROUND(AVG(score_accessibility), 2) AS `score_accessibility`,
                ROUND(AVG(score_best_practice), 2) AS `score_best_practice`,
                ROUND(AVG(score_seo), 2) AS `score_seo`,
                ROUND(AVG(performance_first_contentful_paint), 2) AS `performance_first_contentful_paint`,
                ROUND(AVG(performance_largest_contentful_paint), 2) AS `performance_largest_contentful_paint`,
                ROUND(AVG(performance_total_blocking_time), 2) AS `performance_total_blocking_time`,
                ROUND(AVG(performance_cumulative_layout_shift), 2) AS `performance_cumulative_layout_shift`,
                ROUND(AVG(performance_speed_index), 2) AS `performance_speed_index`,
                COUNT(*) as `count`
            '));
    }

    protected function hasReports(): bool
    {
        if (isset($this->hasReports)) {
            return $this->hasReports;
        }

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
            'chartDetails' => [
                'chart' => [
                    'type' => 'line',
                    'animations' => [
                        'speed' => 300,
                        'animateGradually' => [
                            'delay' => 30
                        ]
                    ],
                ],
                'series' => [
                    [
                        'key' => 'score_performance',
                        'name' => 'Performance',
                        'color' => '#FF6F61',
                        'data' => [],
                    ],
                    [
                        'key' => 'score_accessibility',
                        'name' => 'Accessibility',
                        'color' => '#FFD166',
                        'data' => [],
                    ],
                    [
                        'key' => 'score_best_practice',
                        'name' => 'Best Practice',
                        'color' => '#06D6A0',
                        'data' => [],
                    ],
                    [
                        'key' => 'score_seo',
                        'name' => 'SEO',
                        'color' => '#4D96FF',
                        'data' => [],
                    ],
                    [
                        'key' => 'performance_first_contentful_paint',
                        'name' => 'FCP',
                        'color' => '#845EC2',
                        'data' => [],
                    ],
                    [
                        'key' => 'performance_largest_contentful_paint',
                        'name' => 'LCP',
                        'color' => '#FF9671',
                        'data' => [],
                    ],
                    [
                        'key' => 'performance_total_blocking_time',
                        'name' => 'TBT',
                        'color' => '#00C9A7',
                        'data' => [],
                    ],
                    [
                        'key' => 'performance_cumulative_layout_shift',
                        'name' => 'CLS',
                        'color' => '#F9F871',
                        'data' => [],
                    ],
                    [
                        'key' => 'performance_speed_index',
                        'name' => 'Speed Index',
                        'color' => '#C77DFF',
                        'data' => [],
                    ],
                ],
                'xaxis' => [
                    'type' => 'datetime',
                    'categories' => []
                ]
            ],
            'chartPerformance' => [
                'chart' => [
                    'type' => 'bar',
                    'animations' => [
                        'speed' => 300,
                        'animateGradually' => [
                            'delay' => 30
                        ]
                    ],
                ],
                'series' => [
                    [
                        'name' => 'Performance',
                        'data' => []
                    ]
                ],
                'xaxis' => [
                    'type' => 'datetime',
                ]
            ]
        ];

        $reports = $this->getAveragesBuilder()
            ->addSelect(DB::raw('DATE(created_at) as `created_at`'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('created_at', 'DESC')
            ->limit(60)
            ->get();

        foreach ($reports as $report) {
            // Append chartDetails data
            foreach ($data['chartDetails']['series'] as $index => $series) {
                $data['chartDetails']['series'][$index]['data'][] = (int) ($report->{$series['key']} * 100);
            }
            $data['chartDetails']['xaxis']['categories'][] = $report->created_at->timestamp * 1000;

            // Append chartPerformance data
            $data['chartPerformance']['series'][0]['data'][] = [
                'x' => $report->created_at->timestamp * 1000,
                'y' => (int) ($report->score_performance * 100),
                'fillColor' => $this->scoreToColour($report->score_performance),
                'strokeColor' => '#C23829'
            ];
        }

        return $this->chartDataCache = $data;
    }

    public function scoreToColour(float $score): string
    {
        if ($score >= 0.9) {
            return '#0cce6b';
        }

        if ($score >= 0.5) {
            return '#ffa400';
        }

        return '#ff4e42';
    }
}
