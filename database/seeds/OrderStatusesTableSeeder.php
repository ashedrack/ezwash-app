<?php

use Illuminate\Database\Seeder;
use App\Models\OrdersStatus;

class OrderStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['id' => OrdersStatus::PENDING, 'name' => 'pending'],
            ['id' => OrdersStatus::COMPLETED, 'name' => 'completed']
        ];
        foreach ($statuses as $s){
            OrdersStatus::firstOrCreate([
                'id' => $s['id']
            ], $s);
        }
    }
}
