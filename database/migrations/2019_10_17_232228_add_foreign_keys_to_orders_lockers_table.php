<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

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
			$table->foreign('order_id', 'order_id_fk')->references('id')->on('orders')->onUpdate('NO ACTION')->onDelete('CASCADE');
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
			$table->dropForeign('order_id_fk');
		});
	}

}
