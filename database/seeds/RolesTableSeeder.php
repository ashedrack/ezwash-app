<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'app_developer',
                'hierarchy' => 100,
                'display_name' => 'System Developer',
                'description' => 'Has access to the entire application especially creating/updating roles and permissions'
            ],
            [
                'name' => 'overall_admin',
                'hierarchy' => 50,
                'display_name' => 'Overall Admin',
                'description' => 'Has access to the entire application especially companies creation, modification and deletion'
            ],
            [
                'name' => 'super_admin',
                'hierarchy' => 3,
                'display_name' => 'Super Admin',
                'description' => 'Has access to everything in the assigned company, especially "locations" creation, modification and deletion'
            ],
            [
                'name' => 'dropoff_admin',
                'hierarchy' => 3,
                'display_name' => 'Dropoff Admin',
                'description' => 'Allowed to create dropoff orders'
            ],
            [
                'name' => 'admin',
                'hierarchy' => 2,
                'display_name' => 'Admin',
                'description' => 'Is the administration for a certain location, allowed to add, modify and delete employee details employees'
            ],
            [
                'name' => 'store_manager',
                'hierarchy' => 1,
                'display_name' => 'Store Manager',
                'description' => 'Allowed to add and modify customer and orders'
            ]
        ];
        foreach ($roles as $role){
            Role::updateOrCreate([
                'name' => $role['name']
            ], $role);
        }
    }
}
