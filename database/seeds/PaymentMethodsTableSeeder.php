<?php

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $methods = [
            ['id' => PaymentMethod::CARD_PAYMENT, 'name' => 'card'],
            ['id' => PaymentMethod::CASH_PAYMENT, 'name' => 'cash'],
            ['id' => PaymentMethod::POS_PAYMENT, 'name' => 'pos'],
        ];
        foreach ($methods as $m){
            \App\Models\PaymentMethod::updateOrCreate([
                'id' => $m['id']
            ], $m);
        }
    }
}
