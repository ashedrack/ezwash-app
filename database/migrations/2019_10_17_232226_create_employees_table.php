<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

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
			$table->string('email', 200)->unique('employee_email_UNIQUE');
			$table->string('phone', 14)->unique('employee_phone_UNIQUE');
			$table->string('name', 191);
			$table->string('gender', 50)->nullable();
			$table->string('avatar', 191)->nullable();
			$table->string('password', 191)->nullable();
			$table->string('address', 255)->nullable();
			$table->integer('created_by')->unsigned()->nullable()->index('created_by_fk_idx');
			$table->integer('location_on_create')->nullable()->index('creation_location_idx');
			$table->integer('location_id')->nullable()->index('location_fk_idx');
			$table->integer('company_id')->unsigned()->nullable()->index('employee_company_fk_idx');
			$table->boolean('is_active')->default(1);
			$table->string('remember_token', 100)->nullable();
			$table->timestamp('email_verified_at')->nullable();
			$table->timestamps();
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
