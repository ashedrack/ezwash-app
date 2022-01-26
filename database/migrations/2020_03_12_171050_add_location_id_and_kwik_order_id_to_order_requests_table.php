<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationIdAndKwikOrderIdToOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->integer('location_id')->comment('Ezwash Store to deliver the item if pickup request and pick item from if delivery');
            $table->string('kwik_order_id')->comment('Unique id for the task used for fetching job status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->dropColumn('location_id');
            $table->dropColumn('kwik_order_id');
        });
    }
}
