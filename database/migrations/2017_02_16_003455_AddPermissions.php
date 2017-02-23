<?php

use App\Support\Database\Migration;

class AddPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->addPermission('pkg.abuse.report.read');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->deletePermission('pkg.abuse.report.read');
    }
}
