<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class AddForeignKeysToEmployeesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('employees', function(Blueprint $table)
		{
			$table->foreign('created_by', 'created_by_fk')->references('id')->on('employees')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('company_id', 'employee_company_fk')->references('id')->on('companies')->onUpdate('NO ACTION')->onDelete('CASCADE');
			$table->foreign('location_id', 'location_fk')->references('id')->on('locations')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('employees', function(Blueprint $table)
		{
			$table->dropForeign('created_by_fk');
			$table->dropForeign('employee_company_fk');
			$table->dropForeign('location_fk');
		});
	}

}
