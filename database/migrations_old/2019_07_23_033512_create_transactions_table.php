<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->bigIncrements('id');
			$table->integer('transaction_type_id')->index('transaction_type_fk_idx');
            $table->bigInteger('order_id')->unsigned()->index('transaction_order_fk_idx');
            $table->integer('user_id')->unsigned()->index('transaction_user_fk_idx');
			$table->float('amount', 11);
			$table->text('metadata', 16777215)->nullable();
			$table->text('header', 16777215)->nullable();
			$table->string('message')->nullable();
			$table->integer('transaction_status')->index('transaction_status_id_fk_idx');
			$table->string('reference_code')->nullable()->unique('response_code_UNIQUE');
			$table->integer('status')->nullable()->index('index5');
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
		Schema::drop('transactions');
	}

}
