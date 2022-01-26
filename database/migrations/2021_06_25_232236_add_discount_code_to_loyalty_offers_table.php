<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountCodeToLoyaltyOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loyalty_offers', function (Blueprint $table) {
            $table->integer('spending_requirement')->nullable()->change();
            $table->boolean('is_special_offer')->default(false)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loyalty_offers', function (Blueprint $table) {
           $table->integer('spending_requirement')->change();
            $table->dropColumn('is_special_offer');
        });
    }
}
