<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectPageUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_page_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('project_page_id')->nullable()->index();
            $table->string('fb_user_id')->index();
            $table->boolean('live_chat')->default(false);
            $table->timestamps();

            $table->foreign('project_page_id')->references('id')->on('project_page')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_page_user');
    }
}
