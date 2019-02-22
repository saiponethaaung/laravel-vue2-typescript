<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyKeywordsFiltersResponseAddRuleTalbeRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(env('APP_ENV')!=='testing') {
            Schema::table('keywords_filters_response', function(Blueprint $table) {
                $table->dropForeign('keywords_filters_response_keywords_filters_id_foreign');
                $table->dropColumn('keywords_filters_id');

                $table->unsignedBigInteger('keywords_filters_group_rule_id')->nullable()->index();
                $table->foreign('keywords_filters_group_rule_id')->references('id')->on('keywords_filters_group_rule')->onDelete('cascade');
            });
        }
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
