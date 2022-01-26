<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableOrderRequestIdToKwikPickupsAndDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kwik_pickups_and_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('order_request_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kwik_pickups_and_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('order_request_id')->change();
        });
    }
}
