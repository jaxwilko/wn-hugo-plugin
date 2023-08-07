<?php

namespace Winter\User\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateGroupScheduleTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_group_schedule', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->string('status');
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('jaxwilko_hugo_groups');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_group_schedule');
    }
}
