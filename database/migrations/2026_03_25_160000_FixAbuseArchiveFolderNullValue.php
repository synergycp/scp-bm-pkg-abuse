<?php

use App\Setting\Setting;
use Illuminate\Database\Migrations\Migration;

class FixAbuseArchiveFolderNullValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Setting::where('var', 'pkg.abuse.auth.archive_folder')
            ->whereNull('value')
            ->update(['value' => '']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No-op
    }
}
