<?php

use App\Support\Database\Migration;
use App\Setting\Setting;

class AddAbuseSyncFrequencySetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $group = $this->addSettingGroup('Abuse Reports');
        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.sync_frequency', [
            'validator' => Setting::VALID_INT,
            'value' => '5',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->deleteSetting('pkg.abuse.sync_frequency');
    }
}
