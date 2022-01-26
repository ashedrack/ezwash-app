<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountBeforeDiscountColumnToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('amount')->default(0)->change();
            if (!Schema::hasColumn('orders','amount_before_discount')) {
                $table->float('amount_before_discount')->after('amount')->nullable();
            }
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
            $table->integer('amount')->default(0)->change();
            if (Schema::hasColumn('orders','amount_before_discount')) {
                $table->dropColumn('amount_before_discount');
            }
        });
    }
}
