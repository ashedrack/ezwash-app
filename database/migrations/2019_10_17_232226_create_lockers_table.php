<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateLockersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lockers', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('location_id')->index('locker_location');
			$table->integer('locker_number');
			$table->boolean('occupied')->default(0)->comment('0 means vacant, 1 means occupied');
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
		Schema::drop('lockers');
	}

}
