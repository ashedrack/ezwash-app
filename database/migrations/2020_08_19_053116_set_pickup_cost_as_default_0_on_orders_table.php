<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetPickupCostAsDefault0OnOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('amount')->default(0)->change();
            $table->float('delivery_cost')->default(0)->change();
            $table->float('pickup_cost')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('amount')->default(0)->change();
            $table->float('delivery_cost')->default(0)->change();
            $table->float('pickup_cost')->default(0)->change();
        });
    }
}
