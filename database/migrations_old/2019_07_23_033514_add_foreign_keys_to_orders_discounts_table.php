<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOrdersDiscountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('orders_discounts', function(Blueprint $table)
		{
			$table->foreign('loyalty_offer_id', 'discount_offers_fk')->references('id')->on('loyalty_offers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('order_id', 'discount_order_fk')->references('id')->on('orders')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('order_discount_status_id', 'discount_status_fk')->references('id')->on('order_discount_statuses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('orders_discounts', function(Blueprint $table)
		{
			$table->dropForeign('discount_offers_fk');
			$table->dropForeign('discount_order_fk');
			$table->dropForeign('discount_status_fk');
		});
	}

}
