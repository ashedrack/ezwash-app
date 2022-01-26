<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('orders', function(Blueprint $table)
		{
			$table->foreign('status', 'order_status_fk')->references('id')->on('orders_statuses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('order_type', 'order_type_fk')->references('id')->on('order_types')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('payment_method', 'payment_method')->references('id')->on('payment_methods')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('user_id', 'user_id')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('company_id', 'order_company')->references('id')->on('companies')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('orders', function(Blueprint $table)
		{
			$table->dropForeign('order_status_fk');
			$table->dropForeign('order_type_fk');
			$table->dropForeign('payment_method');
			$table->dropForeign('user_id');
		});
	}

}
