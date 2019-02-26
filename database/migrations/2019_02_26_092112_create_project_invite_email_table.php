<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectInviteEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_invite_email', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('status')->default(0);
            $table->unsignedInteger('project_invite_id')->nullable()->index();
            $table->unsignedInteger('project_user_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('project_invite_id')->references('id')->on('project_invite')->onDelete('cascade');
            $table->foreign('project_user_id')->references('id')->on('project_user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_invite_email');
    }
}
