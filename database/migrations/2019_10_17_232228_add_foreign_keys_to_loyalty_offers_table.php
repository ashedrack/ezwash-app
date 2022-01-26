<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class AddForeignKeysToLoyaltyOffersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('loyalty_offers', function(Blueprint $table)
		{
			$table->foreign('created_by', 'loyalty__created_by')->references('id')->on('employees')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('loyalty_offers', function(Blueprint $table)
		{
			$table->dropForeign('loyalty__created_by');
		});
	}

}
