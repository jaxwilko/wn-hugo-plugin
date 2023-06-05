<?php namespace JaxWilko\Hugo\Models;

use Model;

/**
 * HealthCheck Model
 */
class HealthCheck extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_health_checks';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'status_code',
        'primary_ip',
        'http_version',
        'protocol',
        'content_length',
        'size_download',
        'total_time',
        'ssl_serial_number',
        'ssl_start_date',
        'ssl_expire_date',
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

    public function healthy(): bool
    {
        return $this->status_code === 200;
    }
}
