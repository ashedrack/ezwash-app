<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateOrdersLockersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders_lockers', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->bigInteger('order_id')->unsigned()->index('order_id_fk_idx');
			$table->integer('locker_id')->index('locker_id_fk_idx');
			$table->timestamps();
			$table->softDeletes()->comment('for log purposes');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders_lockers');
	}

}
