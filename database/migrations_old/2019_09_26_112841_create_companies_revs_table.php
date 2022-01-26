<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->string('name', 191)->index('name');
			$table->integer('owner_id')->unsigned()->nullable()->index('company_owner_fk_idx');
			$table->string('description', 191)->nullable();
			$table->boolean('active_status')->default(1)->index('active_status')->comment('1 means active, 0 means deactivated');
			$table->string('ps_live_secret', 191)->nullable();
			$table->string('ps_test_secret', 191)->nullable();
			$table->timestamps();
            $table->softDeletes();
			$table->bigInteger('revision_id', true)->unsigned();
			$table->timestamp('revision_created')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('action')->nullable();
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
