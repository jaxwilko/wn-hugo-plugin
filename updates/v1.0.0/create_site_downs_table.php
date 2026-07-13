<?php

namespace Winter\User\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

class CreateHealthChecksTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_site_downs', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->integer('status_code');
            $table->string('primary_ip');
            $table->longText('response_headers');
            $table->longText('response_body');
            $table->longText('certinfo');
            $table->dateTime('down_at');
            $table->dateTime('up_at')->nullable();
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('jaxwilko_hugo_sites');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_site_downs');
    }
}
