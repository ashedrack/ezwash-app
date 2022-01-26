<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoincrementFieldToLoyaltyOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableIndexes = getTableIndexes('orders_discounts');
        Schema::table('orders_discounts', function (Blueprint $table)  use ($tableIndexes){
            if(array_key_exists('discount_offers_fk', $tableIndexes)) {
                if (env('DB_CONNECTION') !== 'sqlite') {
                    $table->dropForeign('discount_offers_fk');
                } else {
                    $table->dropIndex('discount_offers_fk');
                }
            }
        });
        Schema::table('loyalty_offers', function (Blueprint $table) {
            $table->integer('id', true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loyalty_offers', function (Blueprint $table) {
            //
        });
    }
}
