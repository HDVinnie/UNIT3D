<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthenticationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('failed_login_attempts');

        Schema::create('authentications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username');
            $table->ipAddress('ip_address');
            $table->string('type')->default('login')->index();
            $table->integer('user_id')->index();
            $table->bigInteger('device_id')->unsigned()->index()->nullable();
            $table->timestamps();
        });

        Schema::table('authentications', function (Blueprint $table) {
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authentications');
    }
}
