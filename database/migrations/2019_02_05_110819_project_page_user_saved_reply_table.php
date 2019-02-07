<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProjectPageUserSavedReplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_page_user_saved_reply', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('message');
            $table->unsignedInteger('project_id')->nullable()->index();
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->unsignedInteger('updated_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('project')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('project_user')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('project_user')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_page_user_saved_reply');
    }
}
