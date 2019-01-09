<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFavProjectPageUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_fav_project_page_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_page_user_id')->nullable()->index();
            $table->unsignedInteger('project_user_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('project_page_user_id')->references('id')->on('project_page_user')->onDelete('cascade');
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
        Schema::dropIfExists('user_fav_project_page_user');
    }
}
