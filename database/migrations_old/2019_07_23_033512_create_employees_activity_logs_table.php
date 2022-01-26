<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees_activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url')->nullable();
            $table->integer('activity_type_id')->unsigned()->index('employee_activity_type_fk_idx');
            $table->text('description', 16777215)->nullable();
            $table->integer('employee_id')->unsigned()->index('activity_employee_fk_idx');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->onUpdate( DB::raw('now()::timestamp(0)'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees_activity_logs');
    }
}
