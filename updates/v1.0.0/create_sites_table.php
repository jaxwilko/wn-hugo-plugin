<?php

namespace Winter\User\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jaxwilko_hugo_sites', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('base_url');
            $table->boolean('performance_testing');
            $table->boolean('health_testing');
            $table->boolean('is_down')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaxwilko_hugo_sites');
    }
};
