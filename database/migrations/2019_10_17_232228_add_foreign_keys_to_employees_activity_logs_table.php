<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class AddForeignKeysToEmployeesActivityLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('employees_activity_logs', function(Blueprint $table)
		{
			$table->foreign('employee_id', 'activity_employee_fk')->references('id')->on('employees')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('activity_type_id', 'employee_activity_type_fk')->references('id')->on('activity_types')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('employees_activity_logs', function(Blueprint $table)
		{
			$table->dropForeign('activity_employee_fk');
			$table->dropForeign('employee_activity_type_fk');
		});
	}

}
