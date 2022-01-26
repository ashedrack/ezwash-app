<?php

use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transactionTypes = [
            [
                'description' => 'Order Payment',
                'name' => 'order_payment'
            ],
            [
                'description' => 'New Card',
                'name' => 'new_card'
            ]

        ];
        foreach ($transactionTypes as $s){
            \App\Models\TransactionType::updateOrCreate(
                ['name' => $s['name']], $s
            );
        }
    }
}
