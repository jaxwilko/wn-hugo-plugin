<?php

namespace Winter\User\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jaxwilko_hugo_action_results', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('action_id')->unsigned();
            $table->integer('workflow_result_id')->unsigned();
            $table->integer('status');
            $table->longText('result');
            $table->timestamps();

            $table->foreign('action_id')->references('id')->on('jaxwilko_hugo_actions');
            $table->foreign('workflow_result_id')->references('id')->on('jaxwilko_hugo_workflow_results');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaxwilko_hugo_action_results');
    }
};
