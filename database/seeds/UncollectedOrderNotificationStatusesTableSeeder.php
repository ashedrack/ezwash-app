<?php

use Illuminate\Database\Seeder;

class UncollectedOrderNotificationStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['id' => 1, 'name' => 'pending'],
            ['id' => 2, 'name' => 'processing'],
            ['id' => 3, 'name' => 'collected']
        ];
        \App\Models\UncollectedOrderNotificationStatus::insertOrIgnore($statuses);
    }
}
