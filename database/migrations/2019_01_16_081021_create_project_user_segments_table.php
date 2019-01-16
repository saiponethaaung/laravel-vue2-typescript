<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectUserSegmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_user_segments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('filters')->nullable();
            $table->unsignedInteger('project_user_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('project_user_id')->references('id')->on('project_user')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_user_segments');
    }
}
