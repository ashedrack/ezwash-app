<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLoyaltyOffersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loyalty_offers', function(Blueprint $table)
		{
			$table->integer('id')->primary();
			$table->string('display_name')->index('index3');
			$table->integer('spending_requirement')->index('index4');
			$table->integer('discount_value')->index('index5');
			$table->dateTime('start_date');
			$table->dateTime('end_date');
			$table->boolean('status')->nullable()->default(0)->comment('active 1; inactive 0');
			$table->integer('created_by')->unsigned()->index('loyalty__created_by_idx');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->onUpdate( DB::raw('now()::timestamp(0)'));
			$table->softDeletes();
			$table->index(['start_date','end_date'], 'index6');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('loyalty_offers');
	}

}
