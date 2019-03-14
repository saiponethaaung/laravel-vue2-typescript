<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyChatButtonBlockTableAddAttributeAndValueColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_button_block', function(Blueprint $table) {
            $table->unsignedInteger('attribute_id')->nullable()->index()->after('section_id');
            $table->text('value')->nullable()->after('attribute_id');

            $table->foreign('attribute_id')->references('id')->on('chat_attribute')->onDelete('restrict');
        });
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
