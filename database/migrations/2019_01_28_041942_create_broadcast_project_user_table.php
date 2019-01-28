<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBroadcastProjectUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('broadcast_project_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('project_broadcast_id')->nullable()->index();
            $table->unsignedBigInteger('project_page_user_id')->nullable()->index();
            $table->unsignedInteger('status')->default(0);
            $table->timestamps();

            $table->foreign('project_broadcast_id')->references('id')->on('project_broadcast')->onDelete('cascade');
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
        Schema::dropIfExists('broadcast_project_user');
    }
}
