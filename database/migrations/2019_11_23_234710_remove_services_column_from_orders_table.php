<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveServicesColumnFromOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('services');
        });
        Schema::table('orders_revs', function (Blueprint $table) {
            $table->dropColumn('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('services', 65535)->nullable();
        });
        Schema::table('orders_revs', function (Blueprint $table) {
            $table->text('services', 65535)->nullable();
        });
    }
}
