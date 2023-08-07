<?php

namespace JaxWilko\Hugo\Classes\Test;

use Illuminate\Http\Response;
use JaxWilko\Hugo\Models\Group;
use JaxWilko\Hugo\Models\GroupSchedule;

class ScheduleService
{
    public function registerWebhook(mixed $id): Response
    {
        if (!is_numeric($id) || !($group = Group::find($id))) {
            return new Response(status: 400);
        }

        static::scheduleGroup($group);

        return new Response(status: 200);
    }

    public static function scheduleGroup(Group $group): GroupSchedule
    {
        return $group->scheduled()->save(new GroupSchedule([
            'status' => 'pending'
        ]));
    }
}
