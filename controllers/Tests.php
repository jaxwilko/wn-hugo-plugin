<?php

namespace JaxWilko\Hugo\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Scripts Backend Controller
 */
class Tests extends Controller
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

        BackendMenu::setContext('JaxWilko.Hugo', 'hugo', 'tests');
    }

    public function create()
    {
        $this->bodyClass = 'fancy-layout compact-container breadcrumb-flush breadcrumb-fancy';

        $this->asExtension('FormController')->create();
    }

    public function update($recordId)
    {
        if (!post('_relation_field')) {
            $this->bodyClass = 'fancy-layout compact-container breadcrumb-flush breadcrumb-fancy';
        }

        $this->asExtension('FormController')->update($recordId);
    }
}
