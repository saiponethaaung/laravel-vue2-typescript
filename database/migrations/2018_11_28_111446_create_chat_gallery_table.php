<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatGalleryTable extends Migration
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
      * 0 list
      * 1 gallery
      * 
      */
    public function up()
    {
        Schema::create('chat_gallery', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('sub')->nullable();
            $table->string('image')->nullable();
            $table->string('url')->nullalbe();
            $table->unsignedInteger('type')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('content_id')->nullable();
            $table->timestamps();

            $table->index('content_id');
            $table->index('order');

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
        Schema::dropIfExists('chat_gallery');
    }
}
