<?php namespace JaxWilko\Hugo\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JaxWilko\Hugo\Casts\Serialize;
use JaxWilko\Hugo\Classes\Test\TestEngine;
use Model;

/**
 * TestResult Model
 */
class TestResult extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_test_results';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'test_id',
        'status',
        'result'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'result' => Serialize::class
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
    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [
        'test' => [
            \JaxWilko\Hugo\Models\Test::class,
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            TestEngine::STATUS_OKAY => 'Okay',
            TestEngine::STATUS_GENERAL_ERROR => 'General Error',
            TestEngine::STATUS_UNCAUGHT_ERROR => 'Uncaught Error',
            TestEngine::STATUS_NO_EXIT_ERROR => 'Exit Error',
        };
    }
}
