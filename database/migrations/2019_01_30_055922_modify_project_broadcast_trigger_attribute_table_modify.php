<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProjectBroadcastTriggerAttributeTableModify extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_broadcast_trigger_attribute', function(Blueprint $table) {
            $table->unsignedBigInteger('project_broadcast_id')->nullable()->index()->after('id');

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
        //
    }
}
