<?php

use Illuminate\Database\Seeder;
use App\Models\KwikTaskStatus;

class KwikTaskStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kwikStatuses = [
            ['id' => KwikTaskStatus::UPCOMING, 'name' => 'UPCOMING'],
            ['id' => KwikTaskStatus::STARTED, 'name' => 'STARTED'],
            ['id' => KwikTaskStatus::ENDED, 'name' => 'ENDED'],
            ['id' => KwikTaskStatus::FAILED, 'name' => 'FAILED'],
            ['id' => KwikTaskStatus::ARRIVED, 'name' => 'ARRIVED'],
            ['id' => KwikTaskStatus::UNASSIGNED, 'name' => 'UNASSIGNED'],
            ['id' => KwikTaskStatus::ACCEPTED, 'name' => 'ACCEPTED'],
            ['id' => KwikTaskStatus::DECLINE, 'name' => 'DECLINE'],
            ['id' => KwikTaskStatus::CANCELED, 'name' => 'CANCELED'],
            ['id' => KwikTaskStatus::DELETED, 'name' => 'DELETED'],
        ];
        foreach ($kwikStatuses as $kwikStatus){
            KwikTaskStatus::updateOrCreate([
                'id' => $kwikStatus['id']
            ], $kwikStatus);
        }
    }
}
