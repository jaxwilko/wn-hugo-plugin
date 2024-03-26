<?php

Route::get('hugo/webhook/{id}', ['uses' => 'JaxWilko\Hugo\Classes\Test\ScheduleService@registerWebhook']);
