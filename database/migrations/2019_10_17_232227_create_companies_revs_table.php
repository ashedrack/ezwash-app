<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateCompaniesRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('companies_revs', function(Blueprint $table)
		{
			$table->integer('id')->unsigned();
			$table->string('name', 191);
			$table->integer('owner_id')->unsigned()->nullable();
			$table->string('description', 191)->nullable();
			$table->boolean('is_active')->default(1)->index('is_active');
			$table->string('ps_live_secret', 191)->nullable();
			$table->string('ps_test_secret', 191)->nullable();
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
		Schema::drop('companies_revs');
	}

}
