<?php

namespace Winter\User\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateTestGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_test_groups', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('test_id')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('jaxwilko_hugo_tests');
            $table->foreign('group_id')->references('id')->on('jaxwilko_hugo_groups');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_test_groups');
    }
}
