<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeveloperEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('developer_employees')) {

            Schema::create('developer_employees', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('employee_id')->unsigned()->comment('Developer needs to be an autheticable user');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->onUpdate(DB::raw('now()::timestamp(0)'));

            });
        }
        if(Schema::hasTable('developer_employees')){
            Schema::table('developer_employees', function (Blueprint $table){
                $table->foreign('employee_id')->references('id')->on('employees')
                    ->onUpdate('NO ACTION')->onDelete('NO ACTION');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('developer_employees');
    }
}
