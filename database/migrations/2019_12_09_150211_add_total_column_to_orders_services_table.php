<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalColumnToOrdersServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders_services','total')) {
            Schema::table('orders_services', function (Blueprint $table) {
                $table->float('total', 11, 2)->after('price')->nullable();
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
        if (Schema::hasColumn('orders_services','total')) {
            Schema::table('orders_services', function (Blueprint $table) {
                $table->dropColumn('total');
            });
        }
    }
}
