<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_session', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('identifier');
            $table->string('ip')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('reject_ip')->nullable();
            $table->string('reject_browser')->nullable();
            $table->string('reject_os')->nullable();
            $table->boolean('is_verify')->default(0);
            $table->unsignedInteger('wrong_attempted')->default(0);
            $table->boolean('is_valid')->default(1);
            $table->datetime('last_login');
            $table->timestamps();

            $table->index('parent_id');

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_session');
    }
}
