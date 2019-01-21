<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProjectUserSegmentsFiltersTableAddConditionColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_user_segments_filters', function(Blueprint $table) {
            $table->unsignedInteger('condition')->default(1)->index()->after('filter_type');
            $table->unsignedInteger('chain_condition')->default(1)->after('condition');
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
