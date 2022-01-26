<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocationsRevsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('locations_revs', function(Blueprint $table)
		{
			$table->integer('id');
			$table->string('name', 191)->index('name');
			$table->string('address', 191);
			$table->string('phone', 15)->nullable()->index('phone');
			$table->string('store_image', 191)->nullable();
			$table->integer('number_of_lockers');
			$table->integer('company_id')->unsigned()->index('location_company_fk_idx');
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
		Schema::drop('locations_revs');
	}

}
