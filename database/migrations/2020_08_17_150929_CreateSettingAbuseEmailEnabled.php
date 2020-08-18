<?php

use App\Setting\Setting;
use App\Support\Database\Migration;

class CreateSettingAbuseEmailEnabled
  extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    $group = $this->addSettingGroup('Abuse Reports');
    $this->addSetting(
      $group,
      Setting::TYPE_CHECKBOX,
      'pkg.abuse.email.enabled',
      ['validator' => Setting::VALID_INT, 'value' => true,]
    );
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    $this->deleteSetting('pkg.abuse.email.enabled');
  }
}
