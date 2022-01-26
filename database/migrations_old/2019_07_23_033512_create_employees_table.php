<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmployeesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employees', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('email', 200)->unique('email_UNIQUE');
			$table->string('phone', 14)->unique('phone_UNIQUE');
			$table->string('name');
			$table->string('gender', 50)->nullable();
			$table->string('avatar')->nullable();
			$table->string('password')->nullable();
			$table->integer('created_by')->nullable()->unsigned()->index('created_by_fk_idx');
			$table->integer('location_on_create')->nullable()->index('creation_location_idx');
			$table->integer('location_id')->nullable()->index('location_fk_idx');
            $table->integer('company_id')->unsigned()->nullable()->index('employee_company_fk_idx');
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->onUpdate( DB::raw('now()::timestamp(0)'));
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('employees');
	}

}
