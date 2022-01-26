<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

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
			$table->string('name', 191)->index();
			$table->string('address', 191);
			$table->string('phone', 15)->nullable()->index();
			$table->string('store_image', 191)->nullable();
			$table->integer('number_of_lockers');
			$table->integer('company_id')->unsigned()->index();
			$table->boolean('is_active')->default(0);
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
		Schema::drop('locations_revs');
	}

}
