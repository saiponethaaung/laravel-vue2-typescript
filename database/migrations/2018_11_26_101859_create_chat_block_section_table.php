<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatBlockSectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_block_section', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('block_id')->nullable();
            $table->string('title');
            $table->unsignedInteger('order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('block_id');
            $table->index('title');
            $table->index('order');

            $table->foreign('block_id')->references('id')->on('chat_block')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_block_section');
    }
}
