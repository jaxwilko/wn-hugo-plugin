<?php namespace JaxWilko\Hugo\Models;

use Model;

/**
 * GroupSchedule Model
 */
class GroupSchedule extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_FAILED = 'failed';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_group_schedule';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'status'
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
        'group' => [
            \JaxWilko\Hugo\Models\Group::class,
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function setStatus(string $status): void
    {
        $this->update([
            'status' => $status
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === static::STATUS_PENDING;
    }

    public function isRunning(): bool
    {
        return $this->status === static::STATUS_RUNNING;
    }

    public function isDone(): bool
    {
        return in_array($this->status, [static::STATUS_FINISHED, static::STATUS_FAILED]);
    }

    public function afterSave()
    {
        if ($this->status === static::STATUS_FINISHED) {
            $this->delete();
        }
    }
}
