<?php

namespace JaxWilko\Hugo\Console;

use Backend\Models\User;
use Illuminate\Support\Facades\Log;
use JaxWilko\Hugo\Classes\Health\HealthChecker;
use JaxWilko\Hugo\Models\Site;
use System\Models\EventLog;
use Winter\Storm\Console\Command;
use Winter\Storm\Support\Facades\Mail;

class SiteDownDetector extends Command
{
    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:down-detector';

    /**
     * @var string The console command description.
     */
    protected $description = 'Run site down detector';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $sites = Site::where('health_testing', true)->get();

        $this->withProgressBar($sites, function (Site $site) {
            try {
                if ($siteDown = HealthChecker::run($site)) {
                    $site->update([
                        'is_down' => true,
                    ]);

                    $this->components->error('Sending downtime alert for ' . $site->name);
                    $this->sendDowntimeEmail($site);
                    return;
                }

                if ($site->is_down) {
                    $site->update([
                        'is_down' => false,
                    ]);
                    $this->components->info('Sending uptime alert for ' . $site->name);
                    $this->sendUptimeEmail($site);
                }

            } catch (\Throwable $e) {
                EventLog::addException($e);
                return;
            }
        });

        $this->output->newLine();
    }

    public function sendDowntimeEmail(Site $site): void
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

    public function sendUptimeEmail(Site $site): void
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

    protected function send(array $config): void
    {
        Mail::send('jaxwilko.hugo::mail.notification', $config, function ($message) use ($config) {
            foreach (User::all() as $user) {
                $message->to($user->email, $user->full_name);
            }
            $message->subject($config['title']);
        });
    }
}
