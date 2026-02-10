<?php

namespace JaxWilko\Hugo\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Database\Model;
use Winter\Storm\Support\Str;

/**
 * LighthouseReport Model
 */
class LighthouseReport extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_lighthouse_reports';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'url_id',
        'score_performance',
        'score_accessibility',
        'score_best_practice',
        'score_seo',
        'performance_first_contentful_paint',
        'performance_largest_contentful_paint',
        'performance_total_blocking_time',
        'performance_cumulative_layout_shift',
        'performance_speed_index',
        'report',
    ];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [
        'report',
    ];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [
        'timeline',
        'final_image',
        'full_page_image',
        'human_created_at',
    ];

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
    public $belongsTo = [
        'url' => [
            \JaxWilko\Hugo\Models\SiteUrl::class,
            'key' => 'url_id',
            'otherKey' => 'id'
        ]
    ];

    public function getFinalImageAttribute(): ?string
    {
        return Storage::url(Str::after($this->getAssetPath('final'), storage_path('app')));
    }

    public function getFullPageImageAttribute(): ?string
    {
        return Storage::url(Str::after($this->getAssetPath('full-page'), storage_path('app')));
    }

    public function getTimelineAttribute(): array
    {
        $path = $this->getAssetPath('timeline');

        if (!File::isDirectory($path)) {
            return [];
        }

        $files = array_diff(scandir($path), ['.', '..']);

        $timeline = [];

        foreach ($files as $file) {
            $timeline[substr($file, 0, -4)] = Storage::url(Str::after($path . '/' . $file, storage_path('app')));
        }

        ksort($timeline);

        return $timeline;
    }

    public function getHumanCreatedAtAttribute(): ?string
    {
        return $this->created_at?->format('Y-m-d H:i:s');
    }

    public function hasImages(): bool
    {
        return File::isDirectory($this->getAssetPath());
    }

    public function deleteImages(): bool
    {
        return File::deleteDirectory($this->getAssetPath());
    }

    public function getAssetPath(string $path = ''): string
    {
        $path = storage_path(sprintf('app/hugo/lighthouse/%d', $this->id)) . ($path ? '/' . ltrim($path, '/') : '');

        if ($glob = File::glob($path . '*')) {
            return $glob[0];
        }

        return $path;
    }
}
