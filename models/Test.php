<?php namespace JaxWilko\Hugo\Models;

use Model;

/**
 * Script Model
 */
class Test extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_tests';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'site_id' => 'required',
        'name' => 'required'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [
        'config'
    ];

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
    public $hasMany = [
        'results' => [
            \JaxWilko\Hugo\Models\TestResult::class,
        ]
    ];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [
        'site' => [
            \JaxWilko\Hugo\Models\Site::class,
        ]
    ];
    public $belongsToMany = [
        'groups' => [
            \JaxWilko\Hugo\Models\Group::class,
            'table' => 'jaxwilko_hugo_test_groups',
            'order' => 'name',
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getTargetAttribute(): string
    {
        return rtrim($this->site->base_url . $this->url, '/');
    }

    public function getTestConfigAttribute(): array
    {
        return array_prepend($this->config, [
            '_group' => 'nav',
            'url' => $this->target
        ]);
    }
}
