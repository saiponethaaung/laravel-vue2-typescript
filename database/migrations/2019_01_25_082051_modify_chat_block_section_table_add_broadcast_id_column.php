<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyChatBlockSectionTableAddBroadcastIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_block_section', function(Blueprint $table) {
            $table->unsignedBigInteger('broadcast_id')->nullable()->after('block_id')->index();

            $table->foreign('broadcast_id')->references('id')->on('project_broadcast')->onDelete('restrict');
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
