<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatQuickReplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_quick_reply', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->unsignedInteger('attribute_id')->nullable();
            $table->unsignedInteger('content_id')->nullable();
            $table->string('value');
            $table->timestamps();

            $table->index('attribute_id');
            $table->index('content_id');

            $table->foreign('attribute_id')->references('id')->on('chat_attribute')->onDelete('restrict');
            $table->foreign('content_id')->references('id')->on('chat_block_section_content')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_quick_reply');
    }
}
