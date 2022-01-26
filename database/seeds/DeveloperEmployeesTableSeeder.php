<?php

use Illuminate\Database\Seeder;

class DeveloperEmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $godAdmin = getOverallAdmin('victoria@initsng.com');
        $godAdmin->assignARole('app_developer');

        $godAdmin2 = getOverallAdmin('mclord@initsng.com');
        $godAdmin2->assignARole('app_developer');
    }
}
