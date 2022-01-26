<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('user_id')->unsigned()->index('user_id_idx');
			$table->integer('order_type')->nullable()->index('order_type_fk_idx');
			$table->text('services', 65535)->nullable();
			$table->integer('status')->unsigned()->index('order_status_fk_idx');
			$table->integer('amount')->default(0);
			$table->integer('payment_method')->nullable()->index('payment_method_idx');
			$table->integer('created_by');
			$table->integer('location_id')->index('order_location');
			$table->integer('company_id')->unsigned()->index('order_company_idx');
			$table->boolean('collected')->default(0)->index('collected_idx');
			$table->text('note', 65535)->nullable();
			$table->integer('bags')->nullable();
			$table->timestamps();
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
		Schema::drop('orders');
	}

}
