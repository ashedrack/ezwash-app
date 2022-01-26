<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateActivityLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_logs', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('url', 191)->nullable();
			$table->integer('activity_type_id')->unsigned()->index('activity_type_fk_idx');
			$table->text('description', 65535)->nullable();
			$table->integer('user_id')->unsigned()->nullable()->index('activity_user_fk_idx');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity_logs');
	}

}
