<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumns('users',['email_verified_at', 'is_active'])) {
            Schema::table('users', function (Blueprint $table) {
                $table->tinyInteger('is_active')->after('password')->default(1);
                $table->timestamp('email_verified_at')->nullable();
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
        if(Schema::hasColumns('users',['email_verified_at', 'is_active'])) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
                $table->dropColumn('email_verified_at');
            });
        }
    }
}
