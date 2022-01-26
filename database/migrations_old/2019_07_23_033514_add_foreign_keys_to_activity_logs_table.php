<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToActivityLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('activity_logs', function(Blueprint $table)
		{
			$table->foreign('user_id', 'activity_user_fk')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('activity_type_id', 'activity_type_fk')->references('id')->on('activity_types')->onUpdate('NO ACTION')->onDelete('NO ACTION');

        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('activity_logs', function(Blueprint $table)
		{
			$table->dropForeign('activity_user_fk');
            $table->dropForeign('activity_type_fk');
		});
	}

}
