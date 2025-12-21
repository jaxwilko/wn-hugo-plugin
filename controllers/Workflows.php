<?php

namespace JaxWilko\Hugo\Controllers;

use Backend\Classes\Controller;
use Illuminate\Support\Facades\Redirect;
use JaxWilko\Hugo\Classes\automation\ScheduleService;
use JaxWilko\Hugo\Models\Workflow;
use Winter\Storm\Support\Facades\Flash;

class Workflows extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    public function onSchedule(int $groupId)
    {
        $group = Workflow::find($groupId);

        if (!$group) {
            Flash::error('Group not found');
            return;
        }

        ScheduleService::scheduleGroup($group);
        Flash::success('Group scheduled!');

        return Redirect::refresh();
    }
}
