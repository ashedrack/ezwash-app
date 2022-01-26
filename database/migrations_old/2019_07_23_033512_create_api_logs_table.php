<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateApiLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_logs', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->text('url', 16777215)->nullable();
			$table->string('method', 50)->nullable();
			$table->text('request_header', 16777215)->nullable();
			$table->text('data_param', 16777215)->nullable();
			$table->text('response', 16777215)->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('api_logs');
	}

}
