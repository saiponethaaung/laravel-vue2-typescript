<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProjectBroadcastFilterTableAddSegmentColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_broadcast_filters', function(Blueprint $table) {
            $table->unsignedBigInteger('project_user_segments_id')->nullable()->index()->after('project_broadcast_id');

            $table->foreign('project_user_segments_id')->references('id')->on('project_user_segments')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
