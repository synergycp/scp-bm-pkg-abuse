<?php

use App\Support\Database\Migration;
use Carbon\Carbon;
use Packages\Abuse\App\Report\Report;

class UpdateAbuseReportsCreatedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Report::query()
            ->update([
                'created_at' => Carbon::now(),
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
        //
    }
}
