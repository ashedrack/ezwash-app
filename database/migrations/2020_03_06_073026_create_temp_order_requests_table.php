<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_order_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->integer('location_id')->comment('Ezwash Store to deliver the item if pickup request and pick item from if delivery request');

            $table->string('pickup_name');
            $table->string('pickup_address');
            $table->float('pickup_latitude');
            $table->float('pickup_longitude');
            $table->dateTime('pickup_time');
            $table->string('pickup_phone');

            $table->string('delivery_name');
            $table->string('delivery_address');
            $table->float('delivery_latitude');
            $table->float('delivery_longitude');
            $table->dateTime('delivery_time');
            $table->string('delivery_phone');

            $table->string('request_type')->comment('pickup or delivery');
            $table->longText('note')->nullable(true)->comment('Request Note/Wash Instructions ');
            $table->double('amount')->default(false);
            $table->boolean('scheduled')->default(false);
            $table->boolean('accepted')->nullable();
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
        Schema::dropIfExists('temp_order_requests');
    }
}
