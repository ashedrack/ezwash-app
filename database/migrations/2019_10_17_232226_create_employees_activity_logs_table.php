<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateEmployeesActivityLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employees_activity_logs', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('url', 191)->nullable();
			$table->integer('activity_type_id')->unsigned()->index('employee_activity_type_fk_idx');
			$table->text('description', 65535)->nullable();
			$table->integer('employee_id')->unsigned()->index('activity_employee_fk_idx');
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
		Schema::drop('employees_activity_logs');
	}

}
