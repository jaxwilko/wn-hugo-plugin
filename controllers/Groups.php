<?php namespace JaxWilko\Hugo\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use JaxWilko\Hugo\Classes\Test\ScheduleService;
use JaxWilko\Hugo\Models\Group;
use Winter\Storm\Support\Facades\Flash;

/**
 * Groups Backend Controller
 */
class Groups extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('JaxWilko.Hugo', 'hugo', 'groups');
    }

    public function onSchedule(int $groupId)
    {
        $group = Group::find($groupId);

        if (!$group) {
            Flash::error('Group not found');
            return;
        }

        ScheduleService::scheduleGroup($group);
        Flash::success('Group scheduled!');
    }
}
