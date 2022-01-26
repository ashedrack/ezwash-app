<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLockerNumbersToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('orders', 'locker_numbers')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('locker_numbers')->nullable();
            });
        }


        if(Schema::hasColumn('orders', 'locker_numbers')) {
            \App\Models\Order::has('lockers', '>', 0)->get()
                ->map(function ($order) {
                    $lockersArr = $order->lockers->pluck('locker_number')->toArray();
                    $order->update([
                        'locker_numbers' => json_encode($lockersArr),
                    ]);
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
        if(Schema::hasColumn('orders', 'locker_numbers')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('locker_numbers');
            });
        }
    }
}
