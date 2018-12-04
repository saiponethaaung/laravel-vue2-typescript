<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatQuickReplyBlockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_quick_reply_block', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quick_reply_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->timestamps();
            
            $table->index('section_id');
            $table->index('quick_reply_id');

            $table->foreign('section_id')->references('id')->on('chat_block_section')->onDelete('restrict');
            $table->foreign('quick_reply_id')->references('id')->on('chat_quick_reply')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_quick_reply_block');
    }
}
