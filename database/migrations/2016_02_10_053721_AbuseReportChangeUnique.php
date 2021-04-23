<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AbuseReportChangeUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('abuse_reports', function (Blueprint $table) {
            $table->dropUnique('abuse_reports_msg_id_unique');
            
            $table->unique(['msg_id', 'addr']);
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
            // Breaks rollback after abuse synced because of duplicates.
            //$table->dropUnique('abuse_reports_msg_id_addr_unique');
            //$table->unique('msg_id');
        });
    }
}
