<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->string('name');
			$table->string('address');
			$table->string('phone', 15)->nullable();
			$table->string('store_image')->nullable();
			$table->integer('number_of_lockers');
			$table->integer('company_id')->unsigned()->index('location_company_fk_idx');
            $table->boolean('is_active')->default(0);
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
		Schema::drop('locations');
	}

}
