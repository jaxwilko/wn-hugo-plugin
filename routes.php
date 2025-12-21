<?php

use Illuminate\Support\Facades\Route;

Route::get('hugo/webhook/{id}', ['uses' => 'JaxWilko\Hugo\Classes\Test\ScheduleService@registerWebhook']);
Route::get('test', function () {
    return <<<HTML
        <html>
            <head>
                <title>Test Page</title>
                <!-- <link href="http://nginx/test_missing.css" rel="stylesheet" type="text/css"> -->
            </head>
            <body>
                <h1>Test Page</h1>
                <script>
                    console.error(new Error('test'));
                </script>
            </body>
        </html>
    HTML;
});
