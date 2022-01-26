<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->foreign('order_id', 'order_services_order_fk')->references('id')->on('orders')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('service_id', 'order_services_service_fk')->references('id')->on('services')->onUpdate('NO ACTION')->onDelete('NO ACTION');
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
			$table->dropForeign('order_services_service_fk');
		});
	}

}
