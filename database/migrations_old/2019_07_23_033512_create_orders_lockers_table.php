<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->integer('locker_id')->index('locket_id_idx');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->onUpdate( DB::raw('now()::timestamp(0)'));
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
