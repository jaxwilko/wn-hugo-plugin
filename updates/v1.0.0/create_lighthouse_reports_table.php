<?php

namespace Winter\User\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateLighthouseReportsTable extends Migration
{
    public function up()
    {
        Schema::create('jaxwilko_hugo_lighthouse_url_reports', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('url_id')->unsigned();

            $table->float('performance', 3, 2);

            $table->float('fcp_score', 3, 2);
            $table->float('fcp_value', 16, 8);
            $table->string('fcp_unit', 16);
            $table->string('fcp_display', 16);

            $table->float('lcp_score', 3, 2);
            $table->float('lcp_value', 16, 8);
            $table->string('lcp_unit', 16);
            $table->string('lcp_display', 16);

            $table->float('fmp_score', 3, 2);
            $table->float('fmp_value', 16, 8);
            $table->string('fmp_unit', 16);
            $table->string('fmp_display', 16);

            $table->float('cls_score', 3, 2);
            $table->float('cls_value', 16, 8);
            $table->string('cls_unit', 16);
            $table->string('cls_display', 16);

            $table->float('si_score', 3, 2);
            $table->float('si_value', 16, 8);
            $table->string('si_unit', 16);
            $table->string('si_display', 16);

            $table->float('srt_score', 3, 2);
            $table->float('srt_value', 16, 8);
            $table->string('srt_unit', 16);
            $table->string('srt_display', 30);

            $table->float('fid_score', 3, 2);
            $table->float('fid_value', 16, 8);
            $table->string('fid_unit', 16);
            $table->string('fid_display', 30);

            $table->float('int_score', 3, 2);
            $table->float('int_value', 16, 8);
            $table->string('int_unit', 16);
            $table->string('int_display', 30);

            $table->float('nsl_score', 3, 2)->nullable();
            $table->float('nsl_value', 16, 8);
            $table->string('nsl_unit', 16);
            $table->string('nsl_display', 30);

            $table->float('ucr_score', 3, 2);
            $table->float('ucr_value', 16, 8);
            $table->string('ucr_unit', 16);
            $table->string('ucr_display', 30);

            $table->float('ujc_score', 3, 2);
            $table->float('ujc_value', 16, 8);
            $table->string('ujc_unit', 16);
            $table->string('ujc_display', 30);

            $table->float('ds_score', 3, 2);
            $table->float('ds_value', 16, 8);
            $table->string('ds_unit', 16);
            $table->string('ds_display', 30);

            $table->timestamps();

            $table->foreign('url_id')->references('id')->on('jaxwilko_hugo_lighthouse_urls');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaxwilko_hugo_lighthouse_url_reports');
    }
}
