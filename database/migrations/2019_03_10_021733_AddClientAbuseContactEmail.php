<?php

use App\Support\Database\Blueprint;
use App\Support\Database\Migration;
use Carbon\Carbon;

class AddClientAbuseContactEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('clients', function (Blueprint $table) {
            $table->string('pkg_abuse_contact_email');
            $table->boolean('pkg_abuse_receive_email')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('clients', function (Blueprint $table) {
            $table->dropColumn('pkg_abuse_contact_email');
            $table->dropColumn('pkg_abuse_receive_email');
        });
    }
}
