<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixAbuseArchiveFolderNullValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columns = Schema::getColumnListing('settings');

        // Find the column that stores the setting key.
        $keyColumn = null;
        foreach (['key', 'name', 'var', 'slug'] as $candidate) {
            if (in_array($candidate, $columns)) {
                $keyColumn = $candidate;
                break;
            }
        }

        if (!$keyColumn) {
            return;
        }

        DB::table('settings')
            ->where($keyColumn, 'pkg.abuse.auth.archive_folder')
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
