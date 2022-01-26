<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateOrdersServicesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders_services', function(Blueprint $table)
		{
			$table->integer('id')->primary();
			$table->bigInteger('order_id')->unsigned()->index('order_services_order_fk_idx');
			$table->integer('service_id')->index('order_services_service_fk_idx');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders_services');
	}

}
