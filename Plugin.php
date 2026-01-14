<?php

namespace JaxWilko\Hugo;

use Backend;
use Backend\Classes\Controller;
use Backend\Models\UserRole;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use Winter\Storm\Support\Facades\Config;

/**
 * Hugo Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'jaxwilko.hugo::lang.plugin.name',
            'description' => 'jaxwilko.hugo::lang.plugin.description',
            'author'      => 'JaxWilko',
            'icon'        => 'plugins/jaxwilko/hugo/assets/img/hugo.svg'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        if (
            PluginManager::instance()->hasPlugin('Winter.TailwindUI')
            && $this->app->runningInBackend()
            && Config::get('jaxwilko.hugo::apply_styles', false)
        ) {
            Controller::extend(function (Controller $controller) {
                $controller->addCss('plugins/jaxwilko/hugo/assets/src/css/backend.css');
            });
        }

        $this->registerCommands();
    }

    public function registerCommands(): void
    {
        $this->registerConsoleCommand('hugo.lighthouse', \JaxWilko\Hugo\Console\LighthouseProcess::class);
        $this->registerConsoleCommand('hugo.health', \JaxWilko\Hugo\Console\SiteDownDetector::class);
        $this->registerConsoleCommand('hugo.script', \JaxWilko\Hugo\Console\HugoScript::class);
        $this->registerConsoleCommand('hugo.clear', \JaxWilko\Hugo\Console\HugoClear::class);
        $this->registerConsoleCommand('hugo.script', \JaxWilko\Hugo\Console\HugoScript::class);
        $this->registerConsoleCommand('hugo.schedule', \JaxWilko\Hugo\Console\WorkflowSchedule::class);
        $this->registerConsoleCommand('hugo.process', \JaxWilko\Hugo\Console\WorkflowProcess::class);
        $this->registerConsoleCommand('hugo.install', \JaxWilko\Hugo\Console\HugoInstall::class);
        $this->registerConsoleCommand('hugo.install-chrome', \JaxWilko\Hugo\Console\InstallChrome::class);
    }

    public function registerSchedule($schedule): void
    {
        if (!Config::get('jaxwilko.hugo::config.enable_scheduler', false)) {
            return;
        }

        $schedule->command('hugo:workflow-schedule')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('hugo:workflow-process')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('hugo:down-detector')
            ->cron('*/2 * * * *')
            ->withoutOverlapping();

        $schedule->command('hugo:lighthouse')
            ->cron('30 7,19 * * *')
            ->withoutOverlapping();

        $schedule->command('hugo:clean')
            ->dailyAt('00:30')
            ->withoutOverlapping();
    }

    public function registerMailLayouts(): array
    {
        return [
            'hugo' => 'jaxwilko.hugo::mail.layout-default',
        ];
    }

    public function registerMailPartials(): array
    {
        return [
            'logo'  => 'jaxwilko.hugo::partials.logo-base64',
        ];
    }

    public function registerPermissions(): array
    {
        return [
            'jaxwilko.hugo.sites' => [
                'tab'   => 'Hugo',
                'label' => 'Access sites',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
            'jaxwilko.hugo.scripts' => [
                'tab'   => 'Hugo',
                'label' => 'Access tests',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return [
            'hugo' => [
                'label'       => 'jaxwilko.hugo::lang.plugin.name',
                'url'         => Backend::url('jaxwilko/hugo/sites'),
                'icon'        => 'icon-leaf',
                'iconSvg'     => 'plugins/jaxwilko/hugo/assets/img/hugo.svg',
                'permissions' => ['jaxwilko.hugo.*'],
                'order'       => 500,
                'sideMenu'    => [
                    'sites' => [
                        'label'       => 'Sites',
                        'icon'        => 'icon-sitemap',
                        'url'         => Backend::url('jaxwilko/hugo/sites'),
                        'permissions' => ['jaxwilko.hugo.sites']
                    ],
                    'actions' => [
                        'label' => 'Actions',
                        'icon' => 'icon-code',
                        'url' => Backend::url('jaxwilko/hugo/actions'),
                        'permissions' => ['jaxwilko.hugo.sites']
                    ],
                    'workflows' => [
                        'label' => 'Workflows',
                        'icon' => 'icon-cubes',
                        'url' => Backend::url('jaxwilko/hugo/workflows'),
                        'permissions' => ['jaxwilko.hugo.sites']
                    ],
                ]
            ],
        ];
    }
}
