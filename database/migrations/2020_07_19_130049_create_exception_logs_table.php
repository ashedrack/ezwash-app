<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExceptionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exception_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message')->index();
            $table->string('url')->nullable()->index();
            $table->integer('line')->nullable()->index();
            $table->string('file')->nullable()->index();
            $table->text('trace_string')->nullable();
            $table->text('additional_info')->nullable();
            $table->integer('occurrence_count')->nullable()->default(0)->index('exception_occurrence_count_idx');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exception_logs');
    }
}
