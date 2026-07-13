<?php

namespace JaxWilko\Hugo\Models;

use Winter\Storm\Database\Model;

class WorkflowSchedule extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_FAILED = 'failed';

    public $table = 'jaxwilko_hugo_workflow_schedule';

    protected $guarded = ['*'];

    protected $fillable = [
        'status'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'workflow' => [
            \JaxWilko\Hugo\Models\Workflow::class,
        ]
    ];

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

    public function afterSave(): void
    {
        if ($this->status === static::STATUS_FINISHED) {
            $this->delete();
        }
    }
}
