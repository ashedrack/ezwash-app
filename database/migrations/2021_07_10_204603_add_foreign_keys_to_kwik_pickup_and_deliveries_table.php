<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToKwikPickupAndDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kwik_pickups_and_deliveries', function (Blueprint $table) {
            $table->foreign('user_id', 'pickup_delivery_user_id_fk')->on('users')->references('id')
                ->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('order_request_id', 'pickup_delivery_order_request_id_fk')->on('order_requests')->references('id')
                ->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('order_id', 'pickup_delivery_order_id_fk')->on('orders')->references('id')
                ->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('pickup_task_status', 'pickup_delivery_pickup_status_fk')->on('kwik_task_statuses')->references('id')
                ->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('delivery_task_status', 'pickup_delivery_delivery_status_fk')->on('kwik_task_statuses')->references('id')
                ->onUpdate('NO ACTION')->onDelete('NO ACTION');
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
            $table->dropForeign('pickup_delivery_user_id_fk');
            $table->dropForeign('pickup_delivery_order_request_id_fk');
            $table->dropForeign('pickup_delivery_order_id_fk');
            $table->dropForeign('pickup_delivery_pickup_status_fk');
            $table->dropForeign('pickup_delivery_delivery_status_fk');
        });
    }
}
