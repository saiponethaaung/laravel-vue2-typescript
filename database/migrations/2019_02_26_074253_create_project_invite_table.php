<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectInviteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_invite', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('role')->default(1);
            $table->unsignedInteger('project_id')->nullable();
            $table->unsignedInteger('status')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('project')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_invite');
    }
}
