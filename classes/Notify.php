<?php

namespace JaxWilko\Hugo\Classes;

use Backend\Facades\Backend;
use Backend\Models\User;
use JaxWilko\Hugo\Models\Settings;
use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Models\WorkflowResult;
use Winter\Storm\Support\Facades\Mail;

class Notify
{
    public static function send(array $config, array $users): void
    {
        Mail::send('jaxwilko.hugo::mail.notification', $config, function ($message) use ($config, $users) {
            foreach ($users as $user) {
                $message->to($user['email'], $user['name']);
            }
            $message->subject($config['title']);
        });
    }

    public static function workflow(WorkflowResult $workflowResult): void
    {
        $report = [];
        $sites = [];
        foreach ($workflowResult->results as $result) {
            if (
                $workflowResult->status === 0 && $result->action->notification === 'okay'
                || $workflowResult->status > 0 && $result->action->notification === 'fail'
            ) {
                $report[$result->id] = $result;
                $sites[$result->action->site_id] = true;
            }
        }

        if (empty($report)) {
            return;
        }

        $sites = array_keys($sites);

        $string = '<table style="text-align: left;"><thead><tr><th>Action</th><th>Result</th></tr></thead><tbody>';
        foreach ($workflowResult->results as $result) {
            $string .= sprintf(
                '<tr><td style="padding-right: 15px;">%s</td><td style="color: %s;">%s</td></tr>',
                $result->action->name,
                $result->getStatusColour(),
                $result->getStatusLabel()
            );
            if (isset($report[$result->id]) && ($message = $report[$result->id]->getNotificationMessage())) {
                $string .= sprintf('
                    <tr><th colspan="2">Message</th></tr>
                    <tr><td colspan="2" class="code" style="padding: 10px; background: #cecece">%s</td></tr>
                ', $message);
            }
        }
        $string .= '</tbody></table>';

        $users = [];
        foreach (User::all() as $user) {
            $users[] = [
                'email' => $user->email,
                'name' => $user->full_name
            ];
        }

        foreach (Settings::get('reporting', []) as $report) {
            if (array_intersect($report['sites'], $sites)) {
                $users[] = [
                    'email' => $report['email'],
                    'name' => $report['name']
                ];
            }
        }

        static::send([
            'title'     => 'Workflow ' . ($workflowResult->status > 0 ? 'Failed' : 'Passed'),
            'heading'   => 'Workflow has reported a ' . ($workflowResult->status > 0 ? 'failure' : 'success') . '!',
            'text'      => $string,
            'footer'    => 'Use the following links to find out more:',
            'buttons'   => [
                [
                    'text' => 'Hugo',
                    'href' => config('app.url'),
                ],
                [
                    'text' => 'Report',
                    'href' => Backend::url('jaxwilko/hugo/workflowresults/update/' . $workflowResult->id),
                    'colour' => '#E91E63'
                ]
            ]
        ], $users);
    }

    public static function downAlert(Site $site): void
    {
        static::send([
            'title'     => 'Health Check Down Alert',
            'heading'   => sprintf('%s is showing as DOWN!', $site->name),
            'text'      => 'The site is currently showing as down, this has been the case since our last check.',
            'footer'    => 'The following links may be of use:',
            'buttons'   => [
                [
                    'text' => 'Hugo',
                    'href' => config('app.url'),
                ],
                [
                    'text' => parse_url($site->base_url, PHP_URL_HOST),
                    'href' => $site->base_url,
                    'colour' => '#E91E63'
                ]
            ]
        ], static::getReportingForSite($site));
    }

    public static function upAlert(Site $site): void
    {
        static::send([
            'title'     => 'Health Check Up Alert',
            'heading'   => sprintf('%s is showing as UP!', $site->name),
            'text'      => 'The site is currently showing as up, this has been the case since our last check.',
            'footer'    => 'The following links may be of use:',
            'buttons'   => [
                [
                    'text' => 'Hugo',
                    'href' => config('app.url'),
                ],
                [
                    'text' => parse_url($site->base_url, PHP_URL_HOST),
                    'href' => $site->base_url,
                    'colour' => '#4CAF50'
                ]
            ]
        ], static::getReportingForSite($site));
    }

    protected static function getReportingForSite(Site $site): array
    {
        $users = [];
        foreach (User::all() as $user) {
            $users[] = [
                'email' => $user->email,
                'name' => $user->full_name
            ];
        }

        foreach (Settings::get('reporting', []) as $report) {
            if (in_array($site->id, $report['sites'])) {
                $users[] = [
                    'email' => $report['email'],
                    'name' => $report['name']
                ];
            }
        }

        return $users;
    }
}
