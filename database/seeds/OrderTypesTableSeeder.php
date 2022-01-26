<?php

use Illuminate\Database\Seeder;

class OrderTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orderTypes = [
            [
                'id' => \App\Models\OrderType::SELF_SERVICE,
                'display_name' => 'Self Service',
                'name' => 'self_service'
            ],
            [
                'id' => \App\Models\OrderType::DROP_OFF,
                'display_name' => 'Drop Off',
                'name' => 'drop_off'
            ]

        ];
        foreach ($orderTypes as $s){
            \App\Models\OrderType::firstOrCreate(
                ['id' => $s['id']], $s
            );
        }
    }
}
