<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectBroadcastFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_broadcast_filters', function (Blueprint $table) {
            $table->increments('id');
            // id form project_boradcast
            $table->unsignedBigInteger('project_broadcast_id')->nullable()->index();
            // to identify user attribute, custom attribute and system attribute
            $table->unsignedInteger('filter_type');
            // user attribute type(current there is only one for gender)
            $table->unsignedInteger('user_attribute_type')->nullable();
            // user attribute value
            $table->string('user_attribute_value')->nullable();
            // system attribute (last seen, signed up and last engaged)
            $table->unsignedInteger('system_attribute_type')->nullable();
            // system attribute value
            $table->string('system_attribute_value')->nullable();
            // id from chat_attribute
            $table->unsignedInteger('chat_attribute_id')->nullable()->index();
            // chat attribute value
            $table->string('chat_attribute_value')->nullable();
            // condition like (is, is not, etc...)
            $table->unsignedInteger('condition')->default(1);
            // condition like (and, or)
            $table->unsignedInteger('chain_condition')->default(1);
            $table->timestamps();

            $table->foreign('project_broadcast_id')->references('id')->on('project_broadcast')->onDelete('restrict');
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
        Schema::dropIfExists('project_broadcast_filters');
    }
}
