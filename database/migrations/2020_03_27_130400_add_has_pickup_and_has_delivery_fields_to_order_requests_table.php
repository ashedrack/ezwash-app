<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasPickupAndHasDeliveryFieldsToOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->dropColumn('request_type');

            $table->boolean('has_pickup')->default(false);
            $table->boolean('has_delivery')->default(false);
            $table->unsignedInteger('order_request_type_id')->index();
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
            $table->string('request_type')->comment('pickup or delivery');
            $table->dropColumn('has_pickup');
            $table->dropColumn('has_delivery');
            $table->dropColumn('order_request_type_id');
        });
    }
}
