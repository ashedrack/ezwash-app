<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateLocationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('locations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 191);
			$table->string('address', 191);
			$table->string('phone', 15)->nullable();
			$table->string('store_image', 191)->nullable();
			$table->integer('number_of_lockers');
			$table->integer('company_id')->unsigned()->index('location_company_fk_idx');
			$table->boolean('is_active')->default(0);
			$table->timestamps();
			$table->softDeletes();
			$table->float('longitude', 10, 0)->nullable();
			$table->float('latitude', 10, 0)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('locations');
	}

}
