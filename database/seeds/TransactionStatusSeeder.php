<?php

use Illuminate\Database\Seeder;

class TransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transactionStatus = [
            [
                'id' => 1,
                'description' => 'Pending',
                'name' => 'pending'
            ],
            [
                'id' => 2,
                'description' => 'Completed',
                'name' => 'completed'
            ],
            [
                'id' => 3,
                'description' => 'Failed',
                'name' => 'failed'
            ],
            [
                'id' => 4,
                'description' => 'Refunded',
                'name' => 'refunded'
            ]

        ];
        foreach ($transactionStatus as $s){
            \App\Models\TransactionStatus::updateOrCreate(
                ['id' => $s['id']], $s
            );
        }
    }
}
