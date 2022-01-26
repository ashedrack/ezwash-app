<?php

use Illuminate\Database\Seeder;
use App\Models\AutomatedAction;

class AutomatedActionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actions = [
            [
                'name' => 'process_uncollected_order_notifications',
                'description' => 'Process uncollected order notifications queue',
                'status' => false
            ],
            [
                'name' => 'process_daily_report',
                'description' => 'Send daily revenue email report',
                'status' => false
            ],
            [
                'name' => 'process_monthly_report',
                'description' => 'Send monthly revenue email report',
                'status' => false
            ]
        ];

        foreach ($actions as $action){
            AutomatedAction::firstOrCreate([
                'name' => $action['name']
            ], $action);
        }
    }
}
