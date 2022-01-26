<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_logs', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('url')->nullable();
            $table->integer('activity_type_id')->unsigned()->index('activity_type_fk_idx');
            $table->text('description', 16777215)->nullable();
			$table->integer('user_id')->nullable()->unsigned()->index('activity_user_fk_idx');
			$table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->onUpdate( DB::raw('now()::timestamp(0)'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity_logs');
	}

}
