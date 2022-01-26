<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateOrdersRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders_revs', function(Blueprint $table)
		{
			$table->bigInteger('id')->unsigned();
			$table->integer('user_id')->unsigned()->index();
			$table->integer('order_type')->nullable()->index();
			$table->text('services', 65535)->nullable();
			$table->integer('status')->unsigned()->index();
			$table->integer('amount')->default(0);
			$table->integer('payment_method')->nullable()->index();
			$table->integer('created_by');
			$table->integer('location_id');
			$table->integer('company_id')->unsigned()->index();
			$table->boolean('collected')->default(0)->index();
			$table->text('note', 65535)->nullable();
			$table->integer('bags')->nullable();
			$table->timestamps(0, false);
			$table->softDeletes();
			$table->bigInteger('revision_id', true)->unsigned();
			$table->timestamp('revision_created')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('action', 191)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders_revs');
	}

}
