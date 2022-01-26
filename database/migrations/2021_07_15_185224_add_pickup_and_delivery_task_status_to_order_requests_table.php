<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPickupAndDeliveryTaskStatusToOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('pickup_task_status')->nullable()->default(0)->index('kwik_pickup_task_status_idx');
            $table->unsignedTinyInteger('delivery_task_status')->nullable()->default(0)->index('kwik_delivery_task_status_idx');
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
            $table->dropColumn('pickup_task_status');
            $table->dropColumn('delivery_task_status');
        });
    }
}
