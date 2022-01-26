<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEmployeesRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('employees_revs', function(Blueprint $table)
		{
			$table->foreign('created_by', 'rev_created_by_fk')->references('id')->on('employees')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('location_on_create', 'rev_creation_location')->references('id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('company_id', 'rev_employee_company_fk')->references('id')->on('companies')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('location_id', 'rev_location_fk')->references('id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('employees_revs', function(Blueprint $table)
		{
			$table->dropForeign('rev_created_by_fk');
			$table->dropForeign('rev_creation_location');
			$table->dropForeign('rev_employee_company_fk');
			$table->dropForeign('rev_location_fk');
		});
	}

}
