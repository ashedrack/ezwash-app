<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLoyaltyOfferIdToUsersDiscountIdOnOrdersDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableIndexes = getTableIndexes('orders_discounts');
        Schema::table('orders_discounts', function (Blueprint $table) use ($tableIndexes){
            if(array_key_exists('discount_status_fk', $tableIndexes)) {
                if (env('DB_CONNECTION') !== 'sqlite') {
                    $table->dropForeign('discount_offers_fk');
                } else {
                    $table->dropIndex('discount_offers_fk');
                }
            }
            $table->dropColumn('order_discount_status_id');
            $table->bigInteger('users_discount_id')->unsigned()->after('order_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_discounts', function (Blueprint $table) {
            $table->integer('order_discount_status_id')->nullable()->index('discount_status_fk_idx');
            $table->foreign('order_discount_status_id', 'discount_status_fk')->references('id')->on('order_discount_statuses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->dropColumn('users_discount_id');
        });
    }
}
