<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class AddForeignKeysToPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('permissions', function(Blueprint $table)
		{
			$table->foreign('group_id', 'permission_group_fk')->references('id')->on('permission_groups')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('permissions', function(Blueprint $table)
		{
			$table->dropForeign('permission_group_fk');
		});
	}

}
