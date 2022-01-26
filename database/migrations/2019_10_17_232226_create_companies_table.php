<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('companies', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 191);
			$table->integer('owner_id')->unsigned()->nullable()->index('company_owner_fk_idx');
			$table->string('description', 191)->nullable();
			$table->boolean('is_active')->default(1)->comment('1 means active, 0 means inactive');
			$table->string('ps_live_secret', 191)->nullable();
			$table->string('ps_test_secret', 191)->nullable();
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
		Schema::drop('companies');
	}

}
