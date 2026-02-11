<?php namespace JaxWilko\Hugo\Models;

use Model;

/**
 * HealthCheck Model
 */
class SiteDown extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_site_downs';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'site_id',
        'status_code',
        'primary_ip',
        'response_headers',
        'response_body',
        'certinfo',
        'down_at',
        'up_at',
    ];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'down_at',
        'up_at',
        'created_at',
        'updated_at',
    ];

    protected $jsonable = [
        'certinfo',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'site' => [
            \JaxWilko\Hugo\Models\Site::class,
        ]
    ];

    public function getCodeAttribute(): int|string
    {
        if ($this->status_code === 200) {
            return 200;
        }

        if ($this->status_code > 499 && $this->status_code <= 599) {
            return '5xx';
        }

        return $this->status_code;
    }

    public function getDurationAttribute(): string
    {
        if (!$this->up_at) {
            return '';
        }

        return $this->up_at->diffForHumans($this->down_at, true);
    }
}
