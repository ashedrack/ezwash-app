<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('email', 200)->unique('email_UNIQUE');
			$table->string('phone', 14)->unique('phone_UNIQUE');
			$table->string('name');
			$table->string('gender', 50)->nullable();
			$table->string('avatar')->nullable();
			$table->string('password')->nullable();
			$table->integer('created_by')->unsigned()->nullable()->index('users_created_by_fk_idx');
			$table->integer('location_on_create')->index('users_creation_location_idx');
			$table->integer('location_id')->index('users_location_fk_idx');
			$table->string('notification_player_id')->nullable();
            $table->rememberToken();
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
		Schema::drop('users');
	}

}
