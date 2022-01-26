<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOrdersRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('orders_revs', function(Blueprint $table)
		{
			$table->foreign('status', 'orde_rev_status_fk')->references('id')->on('orders_statuses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('company_id', 'order_rev_company')->references('id')->on('companies')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('payment_method', 'order_rev_payment_method')->references('id')->on('payment_methods')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('order_type', 'order_rev_type_fk')->references('id')->on('order_types')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('user_id', 'order_revuser_id')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('orders_revs', function(Blueprint $table)
		{
			$table->dropForeign('orde_rev_status_fk');
			$table->dropForeign('order_rev_company');
			$table->dropForeign('order_rev_payment_method');
			$table->dropForeign('order_rev_type_fk');
			$table->dropForeign('order_revuser_id');
		});
	}

}
