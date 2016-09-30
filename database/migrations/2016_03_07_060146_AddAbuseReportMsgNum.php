<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\AbuseReportComment;
use App\Models\AbuseReport;

class AddAbuseReportMsgNum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        AbuseReportComment::query()->delete();
        AbuseReport::query()->delete();

        Schema::table('abuse_reports', function (Blueprint $table) {
            $table->integer('msg_num')->unsigned();
            $table->unique(['msg_num', 'addr']);

            $table->string('from', 400);
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
            $table->dropUnique('abuse_reports_msg_num_addr_unique');
            $table->dropColumn('msg_num');

            $table->dropColumn('from');
        });
    }
}
