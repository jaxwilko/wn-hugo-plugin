<?php

namespace Winter\User\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateHealthChecksTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_health_checks', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->integer('status_code');
            $table->string('primary_ip');
            $table->integer('http_version');
            $table->integer('protocol');
            $table->integer('content_length');
            $table->float('size_download');
            $table->float('total_time');
            $table->string('ssl_serial_number');
            $table->dateTime('ssl_start_date');
            $table->dateTime('ssl_expire_date');
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('jaxwilko_hugo_sites');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_health_checks');
    }
}
