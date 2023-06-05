<?php namespace JaxWilko\Hugo\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Sites Backend Controller
 */
class Sites extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('JaxWilko.Hugo', 'hugo', 'sites');

        $this->addJs('$/jaxwilko/hugo/assets/dist/apex.js');
    }
}
