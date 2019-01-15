<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProjectPageUserTableAddMultipleColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_page_user', function(Blueprint $table) {
            $table->string('first_name')->nullable()->after('fb_user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('gender')->nullable()->after('last_name');
            $table->string('image')->nullable()->after('gender');
            $table->string('locale')->nullable()->after('image');
            $table->string('timezone')->nullable()->after('locale');
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
