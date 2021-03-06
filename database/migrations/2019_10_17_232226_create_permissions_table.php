<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreatePermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 191)->unique();
			$table->string('display_name', 191)->nullable();
			$table->string('description', 191)->nullable();
			$table->timestamps();
			$table->integer('group_id')->unsigned()->nullable()->index('permission_group_fk');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('permissions');
	}

}
