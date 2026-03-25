<?php

use App\Support\Database\Migration;
use App\Setting\Setting;

class AddAbuseArchiveFolderSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $group = $this->addSettingGroup('Abuse Reports');
        $this->addSetting(
            $group,
            Setting::TYPE_TEXT,
            'pkg.abuse.auth.archive_folder',
            ['value' => '[Gmail]/All Mail']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->deleteSetting('pkg.abuse.auth.archive_folder');
    }
}
