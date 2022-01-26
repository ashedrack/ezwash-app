<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateEmployeesRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employees_revs', function(Blueprint $table)
		{
			$table->integer('id')->unsigned();
			$table->string('email', 200)->index('email');
			$table->string('phone', 14)->index('phone');
			$table->string('name', 191)->index('name');
			$table->string('gender', 50)->nullable();
			$table->string('avatar', 191)->nullable();
			$table->string('password', 191)->nullable();
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('location_on_create')->nullable()->index();
			$table->integer('location_id')->nullable()->index();
			$table->integer('company_id')->unsigned()->nullable()->index();
			$table->boolean('is_active')->default(0);
			$table->string('remember_token', 100)->nullable();
			$table->timestamps(0, false);
			$table->softDeletes();
			$table->bigInteger('revision_id', true)->unsigned();
			$table->timestamp('revision_created')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('action', 191)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('employees_revs');
	}

}
