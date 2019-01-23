<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectBroadcastTriggerAttributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_broadcast_trigger_attribute', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chat_attribute_id')->nullable();
            $table->unsignedInteger('condition')->default(1);
            $table->string('value');
            $table->timestamps();

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
        Schema::dropIfExists('project_broadcast_trigger_attribute');
    }
}
