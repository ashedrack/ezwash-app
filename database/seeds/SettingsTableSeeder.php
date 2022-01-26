<?php

use App\Models\AutomatedAction;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                "name" => "max_throttle_count",
                "description" => "Throttle count",
                "value" => 10
            ],
            [
                "name" => "hours_before_first_uncollected_notice",
                "description" => "Maximum number of uncollected order notifications to send per order",
                "value" => 10
            ],
            [
                "name" => "firebase_order_last_processed_id",
                "description" => "Last order_id imported from the firebase project",
                "value" => null
            ],
            [
                "name" => "firebase_last_generated_token",
                "description" => "Last generated firebase access token",
                "value" => null
            ]
        ];

        foreach ($settings as $setting){
            \App\Models\Setting::firstOrCreate([
                'name' => $setting['name']
            ], $setting);
        }
    }
}
