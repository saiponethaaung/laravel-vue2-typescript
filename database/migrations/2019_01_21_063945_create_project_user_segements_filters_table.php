<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectUserSegementsFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_user_segments_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_user_segments_id')->nullable()->index();
            $table->unsignedInteger('filter_type');
            $table->unsignedInteger('user_attribute_type')->nullable();
            $table->string('user_attribute_value')->nullable();
            $table->unsignedInteger('system_attribute_type')->nullable();
            $table->string('system_attribute_value')->nullable();
            $table->unsignedInteger('chat_attribute_id')->nullable()->index();
            $table->string('chat_attribute_value')->nullable();
            $table->timestamps();

            $table->foreign('project_user_segments_id')->references('id')->on('project_user_segments')->onDelete('restrict');
            $table->foreign('chat_attribute_id')->references('id')->on('chat_attribute')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_user_segements_filters');
    }
}
