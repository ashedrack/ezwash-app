<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_timelines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_request_id')->index();
            //At the point of making a pickup request an order is not yet created hence nullable
            $table->bigInteger('order_id')->nullable()->index();
            $table->integer('status_id')->index();
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
        Schema::dropIfExists('orders_timelines');
    }
}
