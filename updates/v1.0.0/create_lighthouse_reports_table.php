<?php

namespace Winter\User\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jaxwilko_hugo_lighthouse_reports', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('url_id')->unsigned();

            $table->float('score_performance', 3);
            $table->float('score_accessibility', 3);
            $table->float('score_best_practice', 3);
            $table->float('score_seo', 3);

            $table->float('performance_first_contentful_paint', 3);
            $table->float('performance_largest_contentful_paint', 3);
            $table->float('performance_total_blocking_time', 3);
            $table->float('performance_cumulative_layout_shift', 3);
            $table->float('performance_speed_index', 3);

            $table->longText('report')->nullable();

            $table->timestamps();

            $table->foreign('url_id')->references('id')->on('jaxwilko_hugo_site_urls');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaxwilko_hugo_lighthouse_reports');
    }
};
