<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBotUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('messenger_id');
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->text('image');
            $table->string('locale');
            $table->string('timezone');
            $table->unsignedInteger('gender')->default(2);
            $table->dateTime('last_active')->useCurrent();
            $table->timestamps();

            $table->index('timezone');
            $table->index('locale');
            $table->index('gender');
            $table->index('last_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_user');
    }
}
