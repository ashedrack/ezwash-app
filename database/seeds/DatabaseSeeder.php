<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ServicesTableSeeder::class,
            PaymentMethodsTableSeeder::class,
            OrderStatusesTableSeeder::class,
            OrderTypesTableSeeder::class,
            RolesTableSeeder::class,
            EmployeesTableSeeder::class,
            PermissionGroupsTableSeeder::class,
            PermissionsTableSeeder::class,
            ActivityTypesTableSeeder::class,
            DeveloperEmployeesTableSeeder::class,
            UserDiscountStatusesTableSeeder::class,
            OrderRequestStatusSeeder::class,
            OrderRequestTypesTableSeeder::class,
            UncollectedOrderNotificationStatusesTableSeeder::class,
            AutomatedActionsTableSeeder::class,
            SettingsTableSeeder::class,
            TransactionStatusSeeder::class,
            KwikTaskStatusesTableSeeder::class
        ]);
        if(config('app.env') != 'production' && config('app.INITIAL_SEED')){
            dump('Generating sample companies, locations and orders');
            $this->call([
                CompaniesTableSeeder::class,
                LocationsTableSeeder::class,
                OrdersTableSeeder::class
            ]);
            dump('Initial seed complete');
        }
    }
}
