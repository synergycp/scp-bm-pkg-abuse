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
            $table->increments('id');

            $table->string('author_type');
            $table->integer('author_id')->unsigned();

            $table->integer('abuse_report_id')->unsigned();
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
        Schema::drop('abuse_report_comments');
    }
}
