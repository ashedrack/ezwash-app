<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKwikPickupsAndDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kwik_pickups_and_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->comment('References the users table');
            $table->unsignedBigInteger('order_request_id')->nullable()->comment('References the order_requests table');
            $table->unsignedBigInteger('order_id')->nullable()->comment('References the orders table');
            $table->unsignedInteger('job_status')->index('pickup_delivery_job_status_idx');
            $table->string('unique_order_id')->index('pickup_delivery_unique_order_id_idx');
            $table->string('credits')->nullable();
            $table->integer('vehicle_id')->nullable()->default(0);
            $table->date('date')->nullable()->index('pickup_delivery_date_idx');
            $table->double('total_amount', 10, 2)->nullable()->index('pickup_delivery_total_amount_idx');
            $table->double('actual_amount_paid', 10, 2)->nullable()->index('pickup_delivery_actual_amount_idx');
            $table->tinyInteger('is_return_task')->nullable()->index('pickup_delivery_is_return_task_idx')->default(0);
            $table->tinyInteger('is_multiple_deliveries')->nullable()->default(0);
            $table->string('sender_name')->index('pickup_delivery_sender_name_idx');
            $table->string('pickup_address');
            $table->tinyInteger('pickup_task_status');
            $table->string('pickup_longitude')->index('pickup_delivery_pickup_longitude_idx');
            $table->string('pickup_latitude')->index('pickup_delivery_pickup_latitude_idx');

            $table->string('receiver_name')->index('pickup_delivery_receiver_name_idx');
            $table->string('delivery_address');
            $table->tinyInteger('delivery_task_status');
            $table->string('delivery_longitude')->index('pickup_delivery_delivery_longitude_idx');
            $table->string('delivery_latitude')->index('pickup_delivery_delivery_latitude_idx');
            $table->timestamp('started_datetime')->nullable()->index('pickup_delivery_start_time_idx');
            $table->timestamp('completed_datetime')->nullable()->index('pickup_delivery_completion_time_idx');
            $table->boolean('is_paid_for')->index('pickup_delivery_is_paid_for_idx')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kwik_pickups_and_deliveries');
    }
}
