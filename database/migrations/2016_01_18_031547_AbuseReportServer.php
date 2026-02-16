<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AbuseReportServer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('abuse_reports', function (Blueprint $table) {
            $table->unsignedInteger('server_id')->nullable();
            $table->foreign('server_id')->references('id')->on('servers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('abuse_reports', function (Blueprint $table) {
            $table->dropForeign('abuse_reports_server_id_foreign');
            $table->dropColumn('server_id');
        });
    }
}
