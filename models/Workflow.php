<?php

namespace JaxWilko\Hugo\Models;

use Illuminate\Support\Facades\App;
use Winter\Storm\Database\Model;

/**
 * Group Model
 */
class Workflow extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'jaxwilko_hugo_workflows';

    protected $guarded = ['*'];

    protected $fillable = [];

    public $rules = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $hasMany = [
        'scheduled' => [
            \JaxWilko\Hugo\Models\WorkflowSchedule::class,
        ],
        'results' => [
            \JaxWilko\Hugo\Models\WorkflowResult::class,
        ]
    ];

    public $belongsToMany = [
        'actions' => [
            \JaxWilko\Hugo\Models\Action::class,
            'table' => 'jaxwilko_hugo_action_workflows',
            'order' => 'name',
        ]
    ];

    public function getAttribute($key)
    {
        if ($key === '_webhook') {
            return url(sprintf(
                '/hugo/webhook/%d?token=%s',
                $this->id,
                App::make('encrypter')->encrypt('hugo-' . $this->id)
            ));
        }

        return parent::getAttribute($key);
    }
}
