<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectPageUserAttributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_page_user_attribute', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('attribute_id')->nullable()->index();
            $table->string('value')->index();
            $table->unsignedBigInteger('project_page_user_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('attribute_id')->references('id')->on('chat_attribute')->onDelete('restrict');
            $table->foreign('project_page_user_id')->references('id')->on('project_page_user')->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_page_user_attribute');
    }
}
