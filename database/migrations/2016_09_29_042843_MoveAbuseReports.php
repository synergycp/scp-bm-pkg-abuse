<?php

use App\Support\Database\Migration;
use App\Models\Log;
use Packages\Abuse\App\Report\Report;

class MoveAbuseReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::query()
            ->where('target_type', 'App\Models\AbuseReport')
            ->update([
                'target_type' => Report::class,
            ])
            ;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Log::query()
            ->where('target_type', Report::class)
            ->update([
                'target_type' => 'App\Models\AbuseReport',
            ])
            ;
    }
}
