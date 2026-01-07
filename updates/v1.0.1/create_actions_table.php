<?php

namespace Winter\User\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jaxwilko_hugo_actions', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->string('name');
            $table->string('url');
            $table->longText('config');
            $table->integer('priority')->default(3);
            $table->string('notification')->default('fail');
            $table->text('notification_message')->nullable();
            $table->boolean('auto_screenshot')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('site_id')->references('id')->on('jaxwilko_hugo_sites');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaxwilko_hugo_actions');
    }
};
