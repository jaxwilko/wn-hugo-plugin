<?php

namespace JaxWilko\Hugo\Console;

use Backend\Models\User;
use JaxWilko\Hugo\Classes\Health\HealthChecker;
use JaxWilko\Hugo\Models\Site;
use Log;
use Winter\Storm\Console\Command;
use Winter\Storm\Support\Facades\Mail;

class HugoHealth extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:health';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:health';

    /**
     * @var string The console command description.
     */
    protected $description = 'Run site health check and reporting';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $sites = Site::where('health_testing', true)->get();

        $this->withProgressBar($sites, function (Site $site) {
            try {
                HealthChecker::run($site);
            } catch (\Throwable $e) {
                Log::error('Health checker failed: ' . $e->getMessage());
                return;
            }
        });

        $this->info(PHP_EOL);

        foreach ($sites as $site) {
            $healthChecks = $site->healthChecks()
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            if (count($healthChecks) < 3) {
                continue;
            }

            if (!$healthChecks[0]->healthy() && !$healthChecks[1]->healthy() && $healthChecks[2]->healthy()) {
                $this->error('Sending downtime alert for ' . $site->name);
                $this->sendDowntimeEmail($site);
            }

            if ($healthChecks[0]->healthy() && !$healthChecks[1]->healthy() && !$healthChecks[2]->healthy()) {
                $this->components->info('Sending uptime alert for ' . $site->name);
                $this->sendUptimeEmail($site);
            }
        }
    }

    public function sendDowntimeEmail(Site $site)
    {
        $this->send([
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
        ]);
    }

    public function sendUptimeEmail(Site $site)
    {
        $this->send([
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
        ]);
    }

    protected function send(array $config)
    {
        Mail::send('jaxwilko.hugo::mail.notification', $config, function ($message) use ($config) {
            foreach (User::all() as $user) {
                $message->to($user->email, $user->full_name);
            }
            $message->subject($config['title']);
        });
    }
}
