<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectPersistanceThirdMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_persistance_third_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->unsignedInteger('parent_id');
            $table->unsignedInteger('type')->default(0);
            $table->unsignedInteger('block_id')->nullable();
            $table->text('url')->nullable();
            $table->timestamps();

            $table->index('parent_id');
            $table->index('block_id');

            $table->foreign('parent_id')->references('id')->on('project_persistance_second_menu')->onDelete('restrict');
            $table->foreign('block_id')->references('id')->on('chat_block_section')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_persistance_third_menu');
    }
}
