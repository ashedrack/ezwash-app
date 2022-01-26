<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class AddForeignKeysToOrdersServicesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('orders_services', function(Blueprint $table)
		{
			$table->foreign('order_id', 'order_services_order_fk')->references('id')->on('orders')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('orders_services', function(Blueprint $table)
		{
			$table->dropForeign('order_services_order_fk');
		});
	}

}
