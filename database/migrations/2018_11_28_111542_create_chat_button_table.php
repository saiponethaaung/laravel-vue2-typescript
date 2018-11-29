<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatButtonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

     /**
      * 
      * Action Type
      * 0 block
      * 1 url
      * 2 phone
      * 3 share
      * 4 buy
      * 
      */
    public function up()
    {
        Schema::create('chat_button', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('text')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedInteger('url_size')->default(0);
            $table->unsignedInteger('content_id')->nullable();
            $table->unsignedInteger('gallery_id')->nullable();
            $table->unsignedInteger('action_type')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('content_id');
            $table->index('gallery_id');
            $table->index('order');

            $table->foreign('content_id')->references('id')->on('chat_block_section_content')->onDelete('restrict');
            $table->foreign('gallery_id')->references('id')->on('chat_gallery')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_button');
    }
}
