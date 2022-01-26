<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->bigIncrements('id');
			$table->integer('user_id')->unsigned()->index('user_id_idx');
			$table->integer('order_type')->nullable()->index('order_type_fk_idx');
			$table->json('services')->nullable();
			$table->integer('status')->unsigned()->index('order_status_fk_idx');
			$table->integer('amount')->default(0);
			$table->integer('payment_method')->nullable()->index('payment_method_idx');
			$table->integer('created_by');
            $table->integer('location_id');
            $table->integer('company_id')->unsigned()->index('order_company_idx');
			$table->boolean('collected')->default(0)->index('collected_idx');
			$table->text('note', 16777215)->nullable();
			$table->integer('bags')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->onUpdate( DB::raw('now()::timestamp(0)'));
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
