<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbuseReportComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abuse_report_comments', function (Blueprint $table) {
            $table->id();

            $table->string('author_type');
            $table->unsignedInteger('author_id');

            $table->unsignedInteger('abuse_report_id');
            $table->foreign('abuse_report_id')->references('id')->on('abuse_reports');

            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('abuse_report_comments');
    }
}
