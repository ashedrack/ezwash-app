<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmployeeRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employee_roles', function(Blueprint $table)
		{
			$table->integer('employee_id')->unsigned();
			$table->integer('role_id')->index('emp_roles_role_id_idx');
			$table->primary(['employee_id','role_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('employee_roles');
	}

}
