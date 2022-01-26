<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceAndQtyColsToOrdersServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumns('orders_services', ['quantity', 'price'])){
            Schema::table('orders_services', function (Blueprint $table) {
                $table->integer('quantity')->default(1);
                $table->double('price')->default(0)->comment('Record the price at the time of the order since the price can change');
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
        if(Schema::hasColumns('orders_services', ['quantity', 'price'])){
            Schema::table('orders_services', function (Blueprint $table) {
                $table->dropColumn('quantity');
                $table->dropColumn('price');
            });
        }
    }
}
