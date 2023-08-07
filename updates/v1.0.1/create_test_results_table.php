<?php

namespace Winter\User\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateTestResultsTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_test_results', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('test_id')->unsigned();
            $table->integer('report_id')->unsigned();
            $table->integer('status');
            $table->longText('result');
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('jaxwilko_hugo_tests');
            $table->foreign('report_id')->references('id')->on('jaxwilko_hugo_test_reports');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_test_results');
    }
}
