<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetOrderRequestStatusIdAsNullableOnOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(env('DB_CONNECTION') !== 'sqlite') {
            Schema::table('order_requests', function (Blueprint $table) {
                $table->string('kwik_job_ids')->nullable()->change();
                $table->string('kwik_order_id')->nullable()->change();
                $table->unsignedInteger('order_request_status_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->string('kwik_job_ids')->change();
            $table->string('kwik_order_id')->change();
            $table->unsignedInteger('order_request_status_id')->change();
        });
    }
}
