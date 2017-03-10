<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Database\Migration;

class CreateAbuseReportAutoSuspensionEmailTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->addEmailTemplate(
            'abuse_report_suspended.tpl',
            __DIR__.'/stub/abuse_report_suspended.tpl',
            [
                'subject' => 'Abuse Report Suspended',
            ]
        );

        $this->addEmailTemplate(
            'abuse_report_suspend_warning.tpl',
            __DIR__.'/stub/abuse_report_suspend_warning.tpl',
            [
                'subject' => 'Abuse Report Suspended Warning',
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->removeEmailTemplate('abuse_report_suspended_warning.tpl');
        $this->removeEmailTemplate('abuse_report_suspended.tpl');
    }
}
