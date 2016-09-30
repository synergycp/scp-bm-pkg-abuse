<?php

use App\Support\Database\Migration;

class CreateAbuseReportEmailTemplates
extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->addEmailTemplate(
            'abuse_report.tpl',
            __DIR__.'/stub/abuse_report.tpl',
            [
                'subject' => 'Abuse Report Received',
            ]
        );

        $this->addEmailTemplate(
            'abuse_report_comment.tpl',
            __DIR__.'/stub/abuse_report_comment.tpl',
            [
                'subject' => 'New comment on Abuse Report',
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
        $this->removeEmailTemplate('abuse_report_comment.tpl');
        $this->removeEmailTemplate('abuse_report.tpl');
    }
}
