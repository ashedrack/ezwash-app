<?php

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $godAdmin = getOverallAdmin('victoria@initsng.com');
        $company = \App\Models\Company::firstOrCreate([
            'name' => 'Ezwash Laundromat',
        ],[
            'owner_id' => $godAdmin->id
        ]);
        $employeeAvatar = asset('images/employee_profile_image.png');
        $employees = [
            [
                'name' => 'Victoria Etim',
                'phone' => '+2349021202163',
                'email' => 'victoria@initsng.com',
                'avatar' => asset('images/employee_profile_image1.png'),
                'is_active' => 1,

            ],
            [
                'name' => 'Mclord PM',
                'phone' => '+2347030754382',
                'email' => 'mclord@initsng.com',
                'avatar' => $employeeAvatar,
                'is_active' => 1,

            ],
            [
                'name' => 'Peter DEV',
                'phone' => '+2348027312256',
                'email' => 'peter@initsng.com',
                'avatar' => $employeeAvatar,
                'is_active' => 1,

            ],
            [
                'name' => 'Nicholas QA',
                'phone' => '+2348032069665',
                'email' => 'nicholas@initsng.com',
                'avatar' => $employeeAvatar,
                'is_active' => 1,

            ],
            [
                'name' => 'Clement DEV',
                'phone' => '+2347038370323',
                'email' => 'clement@initsng.com',
                'avatar' => $employeeAvatar,
                'is_active' => 1,

            ],
            [
                'name' => 'Michael DEV',
                'phone' => '+2347066543947',
                'email' => 'michael@initsng.com',
                'avatar' => $employeeAvatar,
                'is_active' => 1,

            ],
            [
                'name' => 'Ebun CLIENT',
                'phone' => '+2349095056365',
                'email' => 'ebun@ezwashndry.com',
                'avatar' => $employeeAvatar,
                'is_active' => 1,
            ],
            [
                'name' => 'Chidozie CLIENT',
                'phone' => '+2348105385274',
                'email' => 'chidozie@ezwashndry.com',
                'avatar' => $employeeAvatar,
                'is_active' => 1,
            ]

        ];

        $overallAdminRole = \App\Models\Role::firstOrCreate([
            'name' => 'overall_admin',
            'hierarchy' => 50,
        ],[
            'display_name' => 'Overall Admin',
            'description' => 'Has access to the entire application especially companies creation, modification and deletion'
        ]);

        $dispatcher = Employee::getEventDispatcher();
        Employee::unsetEventDispatcher();
        foreach ($employees as $employee){
            $existing = Employee::withTrashed()->where('email', $employee['email'])->first();
            if(!empty($existing) && is_null($existing->deleted_at)){
                continue;
            }
            $e = Employee::firstOrCreate([
                'email' => $employee['email']
            ], $employee);

            if(is_null($e->password)){
                $e->update([
                    'password' => bcrypt('employee_password'),
                    'email_verified_at' => now()
                ]);
            }
            if(!$e->hasRole($overallAdminRole->name)){
                $e->attachRole($overallAdminRole);
            }
        }
        Employee::setEventDispatcher($dispatcher);

    }
}
