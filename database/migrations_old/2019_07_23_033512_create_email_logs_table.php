<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->string('email_type')->nullable();
			$table->text('to', 16777215)->nullable();
			$table->text('request_payload', 16777215)->nullable();
			$table->text('response_payload', 16777215)->nullable();
			$table->string('status')->nullable();
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
