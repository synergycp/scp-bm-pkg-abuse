<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AbuseReportsLongerMsgIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('abuse_reports', function (Blueprint $table) {
            $table->dropUnique('abuse_reports_msg_id_addr_unique');
        });

        Schema::table('abuse_reports', function (Blueprint $table) {
            $table->string('msg_id', 1000)->change();
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
            $table->unique(['msg_id', 'addr']);
            $table->string('msg_id', 200)->change();
        });
    }
}
