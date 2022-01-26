<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactions', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('transaction_type_id')->index('transaction_type_fk_idx');
			$table->bigInteger('order_id')->unsigned()->index('transaction_order_fk_idx');
			$table->integer('user_id')->unsigned()->index('transaction_user_fk_idx');
			$table->float('amount', 11);
			$table->text('metadata', 65535)->nullable();
			$table->text('header', 65535)->nullable();
			$table->string('message', 191)->nullable();
			$table->integer('transaction_status')->index('transaction_status_id_fk_idx');
			$table->string('reference_code', 191)->nullable()->unique('response_code_UNIQUE');
			$table->integer('status')->nullable()->index();
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
		Schema::drop('transactions');
	}

}
