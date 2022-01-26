<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLocationsRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('locations_revs', function(Blueprint $table)
		{
			$table->foreign('company_id', 'location_rev_company_fk')->references('id')->on('companies')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('locations_revs', function(Blueprint $table)
		{
			$table->dropForeign('location_rev_company_fk');
		});
	}

}
