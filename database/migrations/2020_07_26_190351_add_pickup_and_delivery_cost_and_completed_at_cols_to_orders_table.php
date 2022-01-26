<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPickupAndDeliveryCostAndCompletedAtColsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('delivery_cost')->nullable()->after('amount')->default(null);
            $table->float('pickup_cost')->after('amount')->nullable()->default(null);
            $table->timestamp('completed_at')->nullable()->default(null)    ;
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
            $table->dropColumn('delivery_cost');
            $table->dropColumn('pickup_cost');
            $table->dropColumn('completed_at');
        });
    }
}
