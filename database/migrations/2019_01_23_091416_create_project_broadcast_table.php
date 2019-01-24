<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectBroadcastTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_broadcast', function (Blueprint $table) {
            $table->bigIncrements('id');
            // id from project table
            $table->unsignedInteger('project_id')->nullable()->index();
            // for day
            $table->unsignedInteger('day')->nullable();
            // for month
            $table->unsignedInteger('month')->nullable();
            // for year
            $table->unsignedInteger('year')->nullable();
            // for time
            $table->string('time')->nullable();
            // to identify interval type like (once, daily, every week, etc...)
            $table->unsignedInteger('interval_type')->nullable();
            // for first interation, last interation or attribute set triggered
            $table->unsignedInteger('duration')->nullable();
            // for trigger duration (minute, hour, day)
            $table->unsignedInteger('duration_type')->nullable();
            // id from project_message_tag table
            $table->unsignedInteger('project_messgage_tag_id')->nullable();
            // to identify first interation, last interation or attribute set triggered
            $table->unsignedInteger('trigger_type')->nullable();
            // to identify send now, schedule and trigger
            $table->unsignedInteger('broadcast_type')->default(1);
            // to set enable and disabled status for trigger and schedule
            $table->boolean('status')->default(true);
            // for send now action only
            $table->boolean('complete')->default(true);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('project')->onDelete('restrict');
            $table->foreign('project_messgage_tag_id')->references('id')->on('project_messgage_tag')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_schedule_mesg');
    }
}
