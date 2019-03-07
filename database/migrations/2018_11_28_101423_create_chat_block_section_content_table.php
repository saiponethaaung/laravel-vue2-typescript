<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatBlockSectionContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    /**
     * 
     * Type
     * 
     * 1: text
     * 2: typing
     * 3: quick reply
     * 4: user input
     * 5: lsit
     * 6: gallery
     * 7: image
     * 
     */
    public function up()
    {
        Schema::create('chat_block_section_content', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('type')->default(0);
            $table->string('text', 640)->nullable();
            $table->string('content', 640)->nullable();
            $table->text('image')->nullable();
            $table->unsignedInteger('duration')->default(5);
            $table->timestamps();

            $table->index('section_id');
            $table->index('order');

            $table->foreign('section_id')->references('id')->on('chat_block_section')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_block_section_content');
    }
}
