<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class AddForeignKeysToLockersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('lockers', function(Blueprint $table)
		{
			$table->foreign('location_id', 'locker_location')->references('id')->on('locations')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('lockers', function(Blueprint $table)
		{
			$table->dropForeign('locker_location');
		});
	}

}
