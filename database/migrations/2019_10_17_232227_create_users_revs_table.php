<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateUsersRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users_revs', function(Blueprint $table)
		{
			$table->integer('id')->unsigned();
			$table->string('email', 200)->index();
			$table->string('phone', 14)->index();
			$table->string('name', 191);
			$table->string('gender', 50)->nullable();
			$table->string('avatar', 191)->nullable();
			$table->string('password', 191)->nullable();
			$table->integer('created_by')->unsigned()->nullable()->index();
			$table->integer('location_on_create')->index();
			$table->integer('location_id')->index();
			$table->string('notification_player_id', 191)->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->timestamps(0, false);
			$table->softDeletes();
			$table->bigInteger('revision_id', true)->unsigned();
			$table->timestamp('revision_created')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('action', 191)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users_revs');
	}

}
