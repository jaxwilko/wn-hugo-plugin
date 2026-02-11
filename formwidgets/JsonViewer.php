<?php

namespace JaxWilko\Hugo\FormWidgets;

use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Backend\Widgets\Form;

class JsonViewer extends FormWidgetBase
{
    /**
     * Renders the widget.
     * @return string
     */
    public function render()
    {
        return $this->makePartial('body');
    }

    public function getSaveValue($value)
    {
        return FormField::NO_SAVE_DATA;
    }
}
