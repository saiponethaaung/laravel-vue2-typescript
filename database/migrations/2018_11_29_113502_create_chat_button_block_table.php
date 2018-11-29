<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatButtonBlockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_button_block', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('button_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->timestamps();
            
            $table->index('section_id');
            $table->index('button_id');

            $table->foreign('section_id')->references('id')->on('chat_block_section')->onDelete('restrict');
            $table->foreign('button_id')->references('id')->on('chat_button')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_button_block');
    }
}
