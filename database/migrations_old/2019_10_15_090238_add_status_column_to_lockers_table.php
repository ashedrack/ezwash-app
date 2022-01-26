<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusColumnToLockersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('lockers', 'occupied')) {
            Schema::table('lockers', function (Blueprint $table) {
                $table->tinyInteger('occupied')->default(0)->comment('0 means vacant, 1 means occupied');
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
        if(Schema::hasColumn('lockers', 'occupied')) {
            Schema::table('lockers', function (Blueprint $table) {
                $table->dropColumn('occupied');
            });
        }
    }
}
