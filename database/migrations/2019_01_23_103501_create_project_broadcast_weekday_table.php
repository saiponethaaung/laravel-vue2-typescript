<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectBroadcastWeekdayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_broadcast_weekday', function (Blueprint $table) {
            $table->increments('id');
            // id from project_broadcast
            $table->unsignedBigInteger('project_broadcast_id')->nullable()->index();
            // for day 1:Mon, 2:Tue, 3:Wed, 4:Thu, 5:Fri, 6:Sat, 7:Sun
            $table->unsignedInteger('days')->default(0);
            // to enable and disable a schedule for day
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('project_broadcast_id')->references('id')->on('project_broadcast')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_broadcast_weekday');
    }
}
