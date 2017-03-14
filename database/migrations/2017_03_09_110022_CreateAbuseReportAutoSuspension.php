<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Database\Migration;
use App\Setting\Setting;

class CreateAbuseReportAutoSuspension extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $group = $this->addSettingGroup('Abuse Reports');
        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.auto_suspension', [
            'validator' => Setting::VALID_INT,
            'value' => '7',
        ]);

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
