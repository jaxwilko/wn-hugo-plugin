<?php

namespace Winter\User\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateLighthouseUrlsTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_lighthouse_urls', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->string('url');
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('jaxwilko_hugo_sites');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_lighthouse_urls');
    }
}
