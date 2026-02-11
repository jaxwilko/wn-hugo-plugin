<?php

namespace JaxWilko\Hugo\Models;

use Carbon\Carbon;
use Winter\Storm\Database\Model;
use Winter\Storm\Support\Facades\DB;

/**
 * Site Model
 */
class Site extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_sites';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'base_url',
        'image',
        'performance_testing',
        'health_testing',
        'is_down',
    ];

    public $attachOne = [
        'image' => \System\Models\File::class
    ];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected ?array $downReportCache = null;

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'urls' => [
            \JaxWilko\Hugo\Models\SiteUrl::class
        ],
        'downs' => [
            \JaxWilko\Hugo\Models\SiteDown::class
        ],
        'actions' => [
            \JaxWilko\Hugo\Models\Action::class,
        ]
    ];

    public function getDownReports(): array
    {
        if ($this->downReportCache) {
            return $this->downReportCache;
        }

        $records = $this->downs()->select([
            DB::raw('DATE(created_at) as created_at_date'),
            'status_code',
            DB::raw('COUNT(*) as count'),
        ])
            ->groupBy('created_at_date')
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        $data = [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'animations' => [
                    'speed' => 300,
                    'animateGradually' => [
                        'delay' => 30
                    ]
                ],
            ],
            'series' => [
                [
                    'name' => 'Outages',
                    'data' => []
                ]
            ],
            'xaxis' => [
                'type' => 'datetime',
            ]
        ];

        if ($records->isEmpty()) {
            return $data;
        }

        foreach ($records as $record) {
            $data['series'][0]['data'][] = [
                'x' => strtotime($record->created_at_date) * 1000,
                'y' => $record->count,
                'fillColor' => '#ff4e42',
                'strokeColor' => '#C23829'
            ];
        }

        return $this->downReportCache = $data;
    }
}
