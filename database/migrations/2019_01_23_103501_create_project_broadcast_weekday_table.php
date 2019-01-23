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
            $table->unsignedBigInteger('project_broadcast_id')->nullable()->index();
            $table->unsignedInteger('days')->default(0);
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
