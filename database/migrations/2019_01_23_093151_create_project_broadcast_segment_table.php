<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectBroadcastSegmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_broadcast_segment', function (Blueprint $table) {
            $table->increments('id');
            // id from project_user_segments
            $table->unsignedBigInteger('project_user_segments_id')->nullable()->index();
            // id from project_broadcast
            $table->unsignedBigInteger('project_broadcast_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('project_user_segments_id')->references('id')->on('project_user_segments')->onDelete('restrict');
            $table->foreign('project_broadcast_id')->references('id')->on('project_broadcast')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_broadcast_segment');
    }
}
