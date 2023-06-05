<?php

namespace Winter\User\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateSitesTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_sites', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('base_url');
            $table->boolean('performance_testing');
            $table->boolean('health_testing');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_sites');
    }
}
