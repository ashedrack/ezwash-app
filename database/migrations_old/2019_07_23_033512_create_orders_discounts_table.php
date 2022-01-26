<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersDiscountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders_discounts', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->bigInteger('order_id')->unsigned()->index('discount_order_fk_idx');
			$table->integer('loyalty_offer_id')->index('discount_offers_fk_idx');
			$table->integer('order_discount_status_id')->nullable()->index('discount_status_fk_idx');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->onUpdate( DB::raw('now()::timestamp(0)'));
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders_discounts');
	}

}
