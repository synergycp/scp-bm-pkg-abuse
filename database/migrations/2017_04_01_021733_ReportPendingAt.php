<?php

use App\Support\Database\Blueprint;
use App\Support\Database\Migration;
use Carbon\Carbon;

class ReportPendingAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('abuse_reports', function (Blueprint $table) {
            $table->timestamp('pending_at')->default(Carbon::now());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('abuse_reports', function (Blueprint $table) {
            $table->dropColumn('pending_at');
        });
    }
}
