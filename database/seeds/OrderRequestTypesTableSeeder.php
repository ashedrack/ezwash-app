<?php

use Illuminate\Database\Seeder;

class OrderRequestTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $requestTypes = [
            ['id' => 1, 'name' => 'pickup'],
            ['id' => 2, 'name' => 'delivery']
        ];

        foreach ($requestTypes as $requestType) {
            \App\Models\OrderRequestType::firstOrCreate($requestType);
        }
    }
}
