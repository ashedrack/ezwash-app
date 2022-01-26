<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupColumnToPermisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('permissions', 'group_id')){
            Schema::table('permissions', function (Blueprint $table) {
                $table->integer('group_id')->unsigned()->nullable()->default(NULL);
            });
        }
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreign('group_id', 'permission_group_fk')->references('id')->on('permission_groups')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('permissions', 'group_id')){
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropForeign('permission_group_fk');
                $table->dropColumn('group_id');
            });
        }
    }
}
