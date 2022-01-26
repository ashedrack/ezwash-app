<?php

use Illuminate\Database\Seeder;

class PermissionGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups = [
            ['name' => 'company', 'display_name' => 'Companies'],
            ['name' => 'location', 'display_name' => 'Locations'],
            ['name' => 'services', 'display_name' => 'Services'],
            ['name' => 'employee', 'display_name' => 'Employees'],
            ['name' => 'customer', 'display_name' => 'Customers'],
            ['name' => 'order', 'display_name' => 'Orders'],
            ['name' => 'loyalty_offer', 'display_name' => 'Loyalty Offers'],
            ['name' => 'statistics_and_reports', 'display_name' => 'Statistics and reports'],
            ['name' => 'general_settings', 'display_name' => 'General Settings'],
            ['name' => 'roles_and_permissions', 'display_name' => 'Roles and Permissions'],
            ['name' => 'transactions', 'display_name' => 'Transactions']
        ];
        foreach ($groups as $group) {
            \App\Models\PermissionGroup::updateOrCreate([
                'name' => $group['name']
            ], $group);
        }
    }
}
