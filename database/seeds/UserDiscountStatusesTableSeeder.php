<?php

use Illuminate\Database\Seeder;

class UserDiscountStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses =  [
            ['name' => 'Unused'],
            ['name' => 'Assigned to order'],
            ['name' => 'Used'],
            ['name' => 'Expired']
        ];
        foreach ($statuses as $status){
            \App\Models\UserDiscountStatus::updateOrCreate([
                'name' => $status['name']
            ]);
        }
    }
}
