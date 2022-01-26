<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOrdersLockersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('orders_lockers', function(Blueprint $table)
		{
			$table->foreign('locker_id', 'locket_id')->references('id')->on('lockers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('order_id', 'order_id_fk')->references('id')->on('orders')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('orders_lockers', function(Blueprint $table)
		{
			$table->dropForeign('locket_id');
			$table->dropForeign('order_id_fk');
		});
	}

}
