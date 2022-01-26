<?php

use Illuminate\Database\Seeder;

class ActivityTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activityTypes = [
            ['name' => 'created_company'],
            ['name' => 'updated_company'],
            ['name' => 'deleted_company'],
            ['name' => 'deactivated_company']
        ];
        foreach ($activityTypes as $activity) {
            \App\Models\ActivityType::firstOrCreate([
                'name' => $activity['name']
            ]);
        }
    }
}
