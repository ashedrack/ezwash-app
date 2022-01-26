<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

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
			$table->bigInteger('id', true)->unsigned();
			$table->text('url', 65535)->nullable();
			$table->string('method', 50)->nullable();
			$table->text('request_header', 65535)->nullable();
			$table->text('data_param', 65535)->nullable();
			$table->text('response', 65535)->nullable();
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
