<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordsFiltersResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords_filters_response', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('type')->default(0);
            $table->text('reply_text')->nullable();
            $table->unsignedInteger('chat_block_section_id')->nullable()->index();
            $table->unsignedBigInteger('keywords_filters_id')->nullable()->index();
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->unsignedInteger('updated_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('chat_block_section_id')->references('id')->on('chat_block_section')->onDelete('cascade');
            $table->foreign('keywords_filters_id')->references('id')->on('keywords_filters')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('project_user')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('project_user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keywords_filters_response');
    }
}
