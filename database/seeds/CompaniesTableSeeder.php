<?php

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $godAdmin = getOverallAdmin('victoria@initsng.com');

        Company::firstOrCreate([
            'id' => Company::EZWASH_MAIN,
        ],[
            'name' => 'Ezwash Laundromat',
            'owner_id' => $godAdmin->id
        ]);

        $superadmin = Role::firstOrCreate([
            'name' => 'super_admin'
        ],[
            'display_name' => 'Super Admin',
            'description' => 'Has access to everything in the assigned company, especially "locations" creation, modification and deletion'
        ]);
        //Create 5 employees to be the owners of 5 companies
        $dispatcher = Employee::getEventDispatcher();
        Employee::unsetEventDispatcher();//Prevent setup password email from being sent to these random emails
        factory(Company::class, 5)->create()
            ->each(function ($company) use ($godAdmin,$superadmin) {
                $employee = factory(Employee::class)->create([
                    'company_id' => $company->id,
                    'created_by' => $godAdmin->id,
                    'email_verified_at' => now()
                ]);
                $employee->attachRole($superadmin); // parameter can be a Role object, array, or id

                $company->update(['owner_id' => $employee->id]);
            });
        Employee::setEventDispatcher($dispatcher);
    }
}
