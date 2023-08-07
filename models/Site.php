<?php namespace JaxWilko\Hugo\Models;

use Carbon\Carbon;
use Model;

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
        'health_testing'
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

    protected ?array $chartDataCache = null;

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'urls' => [
            \JaxWilko\Hugo\Models\LighthouseUrl::class
        ],
        'healthChecks' => [
            \JaxWilko\Hugo\Models\HealthCheck::class
        ],
        'tests' => [
            \JaxWilko\Hugo\Models\Test::class,
        ]
    ];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachMany = [];

    public function getHealthCheckData(): array
    {
        if ($this->chartDataCache) {
            return $this->chartDataCache;
        }

        $records = $this->healthChecks()->select([
            \DB::raw('DATE(created_at) as date_created'),
            'status_code',
            \DB::raw('COUNT(*) as occurrences'),
        ])
            ->whereDate('created_at', '>=', Carbon::today()->subDays(7))
            ->groupBy('date_created', 'status_code')
            ->orderBy('date_created')
            ->get();

        $data = [
            'statues' => [],
            'checks' => []
        ];

        $timestampCache = [];

        foreach ($records as $record) {
            $timestamp = $timestampCache[$record->date_created]
                ?? $timestampCache[$record->date_created] = Carbon::createFromDate($record->date_created)->timestamp * 1000;

            if (!isset($data['checks'][$timestamp])) {
                $data['checks'][$timestamp] = [];
            }

            if (!isset($data['statues'][$record->code])) {
                $data['statues'][$record->code] = match ($record->code) {
                    200 => '#51BBFE',
                    '5xx' => '#FC6471',
                    default => '#F7FE72'
                };
            }

            $data['checks'][$timestamp][$record->code] = $record->occurrences;
        }

        return $this->chartDataCache = $data;
    }
}
