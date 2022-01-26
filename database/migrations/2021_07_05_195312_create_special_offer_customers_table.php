<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecialOfferCustomersTable extends Migration
{
    protected $tableName = 'special_offer_customers';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable($this->tableName)) {
            Schema::create($this->tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id')->index();
                $table->integer('loyalty_offer_id')->index();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        if(Schema::hasTable($this->tableName)) {
            $indexes = getTableIndexes($this->tableName);
            Schema::table($this->tableName, function (Blueprint $table) use ($indexes){
                if(!array_key_exists('special_offer_customers_user_id_fk', $indexes)) {
                    $table->foreign('user_id', 'special_offer_customers_user_id_fk')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
                }
                if(!array_key_exists('special_offer_customers_offer_id_fk', $indexes)) {
                    $table->foreign('loyalty_offer_id', 'special_offer_customers_offer_id_fk')->references('id')->on('loyalty_offers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
                }
                if(!array_key_exists('special_offer_customers_created_by_fk', $indexes)) {
                    $table->foreign('created_by', 'special_offer_customers_created_by_fk')->references('id')->on('employees')->onUpdate('NO ACTION')->onDelete('NO ACTION');
                }
                if(!array_key_exists('offer_user_unique_IDX', $indexes)) {
                    $table->unique(['user_id', 'loyalty_offer_id'], 'offer_user_unique_IDX');
                }
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
        Schema::dropIfExists($this->tableName);
    }
}
