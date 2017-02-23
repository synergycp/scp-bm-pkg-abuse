<?php
/**
 * Created by PhpStorm.
 * User: shayan
 * Date: 2/23/17
 * Time: 8:13 PM
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Database\Migration;

class UpdateSmtpAuthValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table("settings")->where("name","smtp_host")->update(["value"=>"imap.gmail.com"]);
        \DB::table("settings")->where("name","smtp_user")->update(["value"=>"abuse.test.usd@gmail.com"]);
        \DB::table("settings")->where("name","smtp_pass")->update(["value"=>"#Abuse123!"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table("settings")->where("name","smtp_host")->update(["value"=>""]);
        \DB::table("settings")->where("name","smtp_user")->update(["value"=>""]);
        \DB::table("settings")->where("name","smtp_pass")->update(["value"=>""]);
    }
}
