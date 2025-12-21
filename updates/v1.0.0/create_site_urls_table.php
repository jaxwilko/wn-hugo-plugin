<?php

namespace Winter\User\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jaxwilko_hugo_site_urls', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->string('url');
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('jaxwilko_hugo_sites');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaxwilko_hugo_site_urls');
    }
};
