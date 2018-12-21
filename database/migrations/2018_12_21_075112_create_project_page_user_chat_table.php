<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectPageUserChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_page_user_chat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('content_id')->nullable()->index();
            $table->string('post_back')->index();
            $table->boolean('from_platform')->default(false);
            $table->longText('mesg');
            $table->string('mesg_id')->index();
            $table->boolean('is_send')->default(true);
            $table->unsignedInteger('quick_reply_id')->nullable()->index();
            $table->unsignedInteger('user_input_id')->nullable()->index();
            $table->unsignedBigInteger('project_page_user_id')->nullable()->index();
            $table->timestamps();
            
            $table->foreign('content_id')->references('id')->on('chat_block_section_content')->onDelete('set null');
            $table->foreign('quick_reply_id')->references('id')->on('chat_quick_reply')->onDelete('set null');
            $table->foreign('user_input_id')->references('id')->on('chat_user_input')->onDelete('set null');
            $table->foreign('project_page_user_id')->references('id')->on('project_page_user')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_page_user_chat');
    }
}
