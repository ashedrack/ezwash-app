<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->string('phone');
            $table->bigInteger('order_id')->index();
            $table->integer('address_id')->index();
            $table->dateTime('time')->nullable(true);
            $table->string('request_type')->comment('pickup or delivery');
            $table->longText('note')->nullable(true)->comment('Request Note/Wash Instructions ');
            $table->boolean('scheduled')->default(false);
            $table->unsignedInteger('order_request_status_id');
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
        Schema::dropIfExists('order_requests');
    }
}
