<?php

namespace Winter\User\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jaxwilko_hugo_workflow_results', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('workflow_id')->unsigned();
            $table->integer('status');
            $table->longText('result')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaxwilko_hugo_workflow_results');
    }
};
