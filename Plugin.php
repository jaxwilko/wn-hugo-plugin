<?php

namespace JaxWilko\Hugo;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

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
        $this->registerConsoleCommand('hugo.lighthouse', \JaxWilko\Hugo\Console\HugoLighthouse::class);
        $this->registerConsoleCommand('hugo.health', \JaxWilko\Hugo\Console\HugoHealth::class);
        $this->registerConsoleCommand('hugo.script', \JaxWilko\Hugo\Console\HugoScript::class);
        $this->registerConsoleCommand('hugo.clear', \JaxWilko\Hugo\Console\HugoClear::class);
        $this->registerConsoleCommand('hugo.script', \JaxWilko\Hugo\Console\HugoScript::class);
        $this->registerConsoleCommand('hugo.schedule', \JaxWilko\Hugo\Console\HugoGroupSchedule::class);
        $this->registerConsoleCommand('hugo.process', \JaxWilko\Hugo\Console\HugoGroupProcess::class);

        // @TODO: remove
        $this->registerConsoleCommand('hugo.engine.gen', \JaxWilko\Hugo\Console\GenerateEngineInterface::class);
    }

    public function registerFormWidgets()
    {
        return [
            \JaxWilko\Hugo\FormWidgets\LighthouseResults::class => 'lighthouseresults',
            \JaxWilko\Hugo\FormWidgets\ReportResults::class => 'reportresults'
        ];
    }

    public function registerMailLayouts()
    {
        return [
            'hugo' => 'jaxwilko.hugo::mail.layout-default',
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
                        'icon'        => 'icon-cubes',
                        'url'         => Backend::url('jaxwilko/hugo/sites'),
                        'permissions' => ['jaxwilko.hugo.sites']
                    ],
                    'groups' => [
                        'label' => 'Groups',
                        'icon' => 'icon-object-group',
                        'url' => Backend::url('jaxwilko/hugo/groups'),
                        'permissions' => ['jaxwilko.hugo.sites']
                    ],
                    'tests' => [
                        'label' => 'Tests',
                        'icon' => 'icon-code',
                        'url' => Backend::url('jaxwilko/hugo/tests'),
                        'permissions' => ['jaxwilko.hugo.sites']
                    ]
                ]
            ],
        ];
    }
}
