<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AbuseReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abuse_reports', function (Blueprint $table) {
            $table->increments('id');

            $table->string('subject', 200);
            $table->string('msg_id', 200);
            $table->string('addr', 50);
            $table->text('body');

            $table->integer('entity_id')->unsigned()->nullable();
            $table->foreign('entity_id')->references('id')->on('entities');
            $table->integer('client_id')->unsigned()->nullable();
            $table->foreign('client_id')->references('id')->on('clients');

            $table->timestamp('reported_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique('msg_id');
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('abuse_reports');
    }
}
