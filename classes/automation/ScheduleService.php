<?php

namespace JaxWilko\Hugo\Classes\Automation;

use App;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use JaxWilko\Hugo\Models\Workflow;
use JaxWilko\Hugo\Models\WorkflowSchedule;

class ScheduleService
{
    public function registerWebhook(mixed $id): Response
    {
        if (!is_numeric($id) || !($group = Workflow::find($id))) {
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

    public static function scheduleGroup(Workflow $group): WorkflowSchedule
    {
        return $group->scheduled()->save(new WorkflowSchedule([
            'status' => 'pending'
        ]));
    }
}
