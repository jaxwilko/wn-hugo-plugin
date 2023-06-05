<?php

namespace JaxWilko\Hugo\Models;

use File;
use Illuminate\Support\Facades\Storage;
use Model;

/**
 * LighthouseReport Model
 */
class LighthouseReport extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_lighthouse_url_reports';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'url_id',
        'performance',
        'fcp_score',
        'fcp_value',
        'fcp_unit',
        'fcp_display',
        'lcp_score',
        'lcp_value',
        'lcp_unit',
        'lcp_display',
        'fmp_score',
        'fmp_value',
        'fmp_unit',
        'fmp_display',
        'cls_score',
        'cls_value',
        'cls_unit',
        'cls_display',
        'si_score',
        'si_value',
        'si_unit',
        'si_display',
        'srt_score',
        'srt_value',
        'srt_unit',
        'srt_display',
        'fid_score',
        'fid_value',
        'fid_unit',
        'fid_display',
        'int_score',
        'int_value',
        'int_unit',
        'int_display',
        'nsl_score',
        'nsl_value',
        'nsl_unit',
        'nsl_display',
        'ucr_score',
        'ucr_value',
        'ucr_unit',
        'ucr_display',
        'ujc_score',
        'ujc_value',
        'ujc_unit',
        'ujc_display',
        'ds_score',
        'ds_value',
        'ds_unit',
        'ds_display'
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

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [
        'url' => [
            \JaxWilko\Hugo\Models\LighthouseUrl::class,
            'key' => 'url_id',
            'otherKey' => 'id'
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getFinishedAttribute(): string
    {
        return Storage::url(sprintf('lighthouse/%d/finished.jpg', $this->id));
    }

    public function getTimelineAttribute(): array
    {
        $path = storage_path(sprintf('app/lighthouse/%d/timeline', $this->id));

        if (!is_dir($path)) {
            return [];
        }

        $files = array_diff(scandir($path), ['.', '..']);

        $timeline = [];

        foreach ($files as $file) {
            $timeline[substr($file, 0, -4)] = Storage::url(sprintf('lighthouse/%d/timeline/%s', $this->id, $file));
        }

        ksort($timeline);

        return $timeline;
    }

    public function hasImages(): bool
    {
        $path = storage_path(sprintf('app/lighthouse/%d', $this->id));
        return File::isDirectory($path);
    }

    public function deleteImages(): bool
    {
        $path = storage_path(sprintf('app/lighthouse/%d', $this->id));
        return File::deleteDirectory($path);
    }
}
