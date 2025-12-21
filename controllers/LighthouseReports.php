<?php

namespace JaxWilko\Hugo\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;

/**
 * Lighthouse Reports Backend Controller
 */
class LighthouseReports extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    /**
     * @var array Permissions required to view this page.
     */
    protected $requiredPermissions = [
        'jaxwilko.hugo.lighthousereports.manage_all',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->bodyClass = 'hugo-app';

        $this->addJs('$/jaxwilko/hugo/assets/dist/apex.js');
        $this->addVite(['assets/src/css/jaxwilko-hugo.css', 'assets/src/js/jaxwilko-hugo.js'], 'jaxwilko.hugo');
    }
}
