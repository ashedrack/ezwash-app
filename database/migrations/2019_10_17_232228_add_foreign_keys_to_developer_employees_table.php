<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class AddForeignKeysToDeveloperEmployeesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('developer_employees', function(Blueprint $table)
		{
			$table->foreign('employee_id')->references('id')->on('employees')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('developer_employees', function(Blueprint $table)
		{
			$table->dropForeign('developer_employees_employee_id_foreign');
		});
	}

}
