<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Database\Migration;
use App\Setting\Setting;

class CreateSettingsAbuseThreshold extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $group = $this->addSettingGroup('Abuse Reports');
        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.report.threshold', [
            'validator' => Setting::VALID_INT,
        ]);

        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.auth.host');
        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.auth.user', [
            'validator' => Setting::VALID_EMAIL,
        ]);
        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.auth.pass');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->deleteSettingGroup('Abuse Reports');
    }
}
