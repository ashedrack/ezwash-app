<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActualAndRoudedAmountToOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->float('amount');
            $table->float('actual_estimate');
            $table->string('kwik_job_ids');
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
            $table->dropColumn('amount');
            $table->dropColumn('actual_estimate');
            $table->dropColumn('kwik_job_ids');
        });
    }
}
