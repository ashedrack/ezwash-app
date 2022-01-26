<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users_revs', function(Blueprint $table)
		{
			$table->foreign('created_by', 'users_rev_created_by_fk')->references('id')->on('employees')->onUpdate('NO ACTION')->onDelete('RESTRICT');
			$table->foreign('location_on_create', 'users_rev_creation_location_fk')->references('id')->on('locations')->onUpdate('NO ACTION')->onDelete('RESTRICT');
			$table->foreign('location_id', 'users_rev_location_fk')->references('id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users_revs', function(Blueprint $table)
		{
			$table->dropForeign('users_rev_created_by_fk');
			$table->dropForeign('users_rev_creation_location_fk');
			$table->dropForeign('users_rev_location_fk');
		});
	}

}
