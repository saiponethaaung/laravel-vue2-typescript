<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFacebookRequestLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facebook_request_logs', function(Blueprint $table) {
            $table->boolean('is_income')->default(false)->after('id');
            $table->boolean('is_payload')->default(false)->after('id');
            $table->boolean('is_echo')->default(false)->after('id');
            $table->boolean('is_read')->default(false)->after('is_echo');
            $table->boolean('is_deliver')->default(false)->after('is_echo');
            $table->boolean('fb_request')->default(false)->after('is_echo');
            $table->boolean('fb_response')->default(false)->after('is_echo');
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
