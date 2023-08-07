<?php namespace JaxWilko\Hugo\Models;

use Model;

/**
 * Group Model
 */
class Group extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_groups';

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
    public $hasMany = [
        'scheduled' => [
            \JaxWilko\Hugo\Models\GroupSchedule::class,
        ],
        'reports' => [
            \JaxWilko\Hugo\Models\TestReport::class,
        ]
    ];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [];
    public $belongsToMany = [
        'tests' => [
            \JaxWilko\Hugo\Models\Test::class,
            'table' => 'jaxwilko_hugo_test_groups',
            'order' => 'name',
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getAttribute($key)
    {
        if ($key === '_webhook') {
            return url('/hugo/webhook/' . $this->id);
        }

        return parent::getAttribute($key);
    }
}
