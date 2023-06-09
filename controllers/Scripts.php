<?php namespace JaxWilko\Hugo\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Scripts Backend Controller
 */
class Scripts extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('JaxWilko.Hugo', 'hugo', 'scripts');
    }
}
