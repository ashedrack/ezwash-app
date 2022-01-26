<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTempNextStatusColumnToOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->integer('temp_next_delivery_job_status')->nullable();
            $table->integer('temp_next_pickup_job_status')->nullable();
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
            $table->dropColumn('temp_next_delivery_job_status');
            $table->dropColumn('temp_next_pickup_job_status');
        });
    }
}
