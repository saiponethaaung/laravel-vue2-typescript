<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatUserInputTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_user_input', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question');
            $table->unsignedInteger('content_id')->nullable();
            $table->unsignedInteger('validation')->nullable();
            $table->unsignedInteger('attribute_id')->nullable();
            $table->timestamps();

            $table->index('content_id');
            $table->index('validation');
            $table->index('attribute_id');

            $table->foreign('content_id')->references('id')->on('chat_block_section_content')->onDelete('restrict');
            $table->foreign('attribute_id')->references('id')->on('chat_attribute')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_user_input');
    }
}
