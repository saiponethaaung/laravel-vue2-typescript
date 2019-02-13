<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordsFiltersGroupRuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords_filters_group_rule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('keywords_filters_group_id')->nullable()->index();
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->unsignedInteger('updated_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('keywords_filters_group_id')->references('id')->on('keywords_filters_group')->onDelete('cascade');
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
        Schema::dropIfExists('keywords_filters_group_rule');
    }
}
