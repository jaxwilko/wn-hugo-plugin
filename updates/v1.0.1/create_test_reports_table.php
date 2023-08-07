<?php

namespace Winter\User\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateScriptReportsTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_test_reports', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->integer('status');
            $table->longText('result')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_test_reports');
    }
}
