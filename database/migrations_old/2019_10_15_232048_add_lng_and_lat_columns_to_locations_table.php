<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLngAndLatColumnsToLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumns('locations', ['longitude', 'latitude'])) {
            Schema::table('locations', function (Blueprint $table) {
                $table->double('longitude', 3)->nullable();
                $table->double('latitude', 3)->nullable();
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
        if(Schema::hasColumns('locations', ['longitude', 'latitude'])) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropColumn('longitude')->nullable();
                $table->dropColumn('latitude')->nullable();
            });
        }
    }
}
