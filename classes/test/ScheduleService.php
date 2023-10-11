<?php

namespace JaxWilko\Hugo\Classes\Test;

use App;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use JaxWilko\Hugo\Models\Group;
use JaxWilko\Hugo\Models\GroupSchedule;

class ScheduleService
{
    public function registerWebhook(mixed $id): Response
    {
        if (!is_numeric($id) || !($group = Group::find($id))) {
            return new Response(status: 400);
        }

        $token = Request::get('token');

        if (!$token) {
            return new Response(status: 400);
        }

        try {
            $token = App::make('encrypter')->decrypt($token);
        } catch (\Throwable $e) {
            return new Response(status: 401);
        }

        if (!$token || $token !== 'hugo-' . $id) {
            return new Response(status: 401);
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
