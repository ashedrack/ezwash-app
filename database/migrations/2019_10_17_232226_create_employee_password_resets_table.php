<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateEmployeePasswordResetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employee_password_resets', function(Blueprint $table)
		{
			$table->string('email', 191)->index();
			$table->string('token', 191);
			$table->dateTime('created_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('employee_password_resets');
	}

}
