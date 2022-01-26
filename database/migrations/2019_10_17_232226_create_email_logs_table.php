<?php

use Illuminate\Database\Migrations\Migration;
use App\Classes\Database\Blueprint;

class CreateEmailLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_logs', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('email_type', 191)->nullable();
			$table->text('to', 65535)->nullable();
			$table->text('request_payload', 65535)->nullable();
			$table->text('response_payload', 65535)->nullable();
			$table->string('status', 191)->nullable();
			$table->boolean('multiple_recipients')->nullable()->default(0)->comment('1 if multiple email recipients, 0 if single');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('email_logs');
	}

}
