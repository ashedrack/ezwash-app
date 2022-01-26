<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->string('email', 200)->index('email');
			$table->string('phone', 14)->index('phoneE');
			$table->string('name', 191);
			$table->string('gender', 50)->nullable();
			$table->string('avatar', 191)->nullable();
			$table->string('password', 191)->nullable();
			$table->integer('created_by')->unsigned()->nullable()->index('users_created_by_fk_idx');
			$table->integer('location_on_create')->index('users_creation_location_idx');
			$table->integer('location_id')->index('users_location_fk_idx');
			$table->string('notification_player_id', 191)->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->bigInteger('revision_id', true)->unsigned();
			$table->timestamp('revision_created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('action')->nullable();
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
