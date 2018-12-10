<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectPersistanceMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_persistance_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->unsignedInteger('project_id')->nullable();
            $table->unsignedInteger('type')->default(0);
            $table->unsignedInteger('block_id')->nullable();
            $table->text('url')->nullable();
            $table->timestamps();

            $table->index('project_id');

            $table->foreign('project_id')->references('id')->on('project')->onDelete('cascade');
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
        Schema::dropIfExists('project_persistance_menu');
    }
}
