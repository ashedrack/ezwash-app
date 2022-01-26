<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyIdFieldToLoyaltyOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('loyalty_offers', 'company_id')) {
            Schema::table('loyalty_offers', function (Blueprint $table) {
                $table->integer('company_id')->unsigned()->index();
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
        if(Schema::hasColumn('loyalty_offers', 'company_id')) {
            Schema::table('loyalty_offers', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
}
