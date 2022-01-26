<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $overall_admin_role = Role::firstOrCreate([
            'name' => 'overall_admin'
        ],[
            'hierarchy' => 50,
            'display_name' => 'Overall Admin',
            'description' => 'Has access to everything in the assigned company, especially "locations" creation, modification and deletion'
        ]);
        $dev_role = Role::updateOrCreate([
            'name' => 'app_developer',
        ],[
            'hierarchy' => 100,
            'display_name' => 'System Developer',
            'description' => 'Has access to the entire application especially creating/updating roles and permissions'
        ]);
//        $routes = Route::getRoutes()->getRoutesByName();
//        foreach ($routes as $name => $p){
//            $permission = Permission::firstOrCreate([
//                'name' => $p->uri
//            ], [
//                'display_name' => $name
//            ]);
//            if(!$overall_admin_role->hasPermission($permission->name)){
//                $overall_admin_role->attachPermission($permission);
//            };
//        }


        $groupedPermissions = [
            'company' => [
                [
                    'name' => 'create_company',
                    'display_name' => 'Create Company',
                    'description' => 'Can create a company'
                ],
                [
                    'name' => 'edit_company',
                    'display_name' => 'Edit Company',
                    'description' => 'Can edit company information'
                ],
                [
                    'name' => 'deactivate_company',
                    'display_name' => 'Deactivate Company',
                    'description' => 'Can deactivate a company'
                ],
                [
                    'name' => 'delete_company',
                    'display_name' => 'Delete Company',
                    'description' => 'Can delete a company'
                ],
                [
                    'name' => 'delete_company_permanently',
                    'display_name' => 'Delete Company Permanently',
                    'description' => 'Can delete a company permanently'
                ],
                [
                    'name' => 'list_companies',
                    'display_name' => 'List Companies',
                    'description' => 'Can view all companies'
                ]
            ],
            'location' => [
                [
                    'name' => 'create_location',
                    'display_name' => 'Create Location',
                    'description' => 'Can create a location'
                ],
                [
                    'name' => 'edit_location',
                    'display_name' => 'Edit Location',
                    'description' => 'Can edit location information'
                ],
                [
                    'name' => 'deactivate_location',
                    'display_name' => 'Deactivate Location',
                    'description' => 'Can deactivate a location'
                ],
                [
                    'name' => 'delete_location',
                    'display_name' => 'Delete Location',
                    'description' => 'Can delete a location'
                ],
                [
                    'name' => 'delete_location_permanently',
                    'display_name' => 'Delete Location Permanently',
                    'description' => 'Can delete a location permanently'
                ],
                [
                    'name' => 'list_locations',
                    'display_name' => 'List Locations',
                    'description' => 'Can view list of location'
                ]
            ],

            'employee' => [
                [
                    'name' => 'create_employee',
                    'display_name' => 'Create Employee',
                    'description' => 'Can create a employee'
                ],
                [
                    'name' => 'edit_employee',
                    'display_name' => 'Edit Employee',
                    'description' => 'Can edit employee information'
                ],
                [
                    'name' => 'deactivate_employee',
                    'display_name' => 'Deactivate Employee',
                    'description' => 'Can deactivate a employee'
                ],
                [
                    'name' => 'delete_employee',
                    'display_name' => 'Delete Employee',
                    'description' => 'Can delete an employee'
                ],
                [
                    'name' => 'delete_employee_permanently',
                    'display_name' => 'Delete Employee Permanently',
                    'description' => 'Can delete an employee permanently'
                ],
                [
                    'name' => 'list_employees',
                    'display_name' => 'List Employees',
                    'description' => 'Can view list of employees'
                ]
            ],

            'services' => [
                [
                    'name' => 'list_services',
                    'display_name' => 'List Services',
                    'description' => 'Can view a list of services'
                ],
                [
                    'name' => 'create_service',
                    'display_name' => 'Create Service',
                    'description' => 'Can create a service'
                ],
                [
                    'name' => 'edit_service',
                    'display_name' => 'Edit Service',
                    'description' => 'Can edit service information'
                ],
                [
                    'name' => 'delete_service',
                    'display_name' => 'Delete Service',
                    'description' => 'Can delete a service'
                ]
            ],

            'customer' => [
                [
                    'name' => 'create_customer',
                    'display_name' => 'Create Customer',
                    'description' => 'Can create a customer'
                ],
                [
                    'name' => 'edit_customer',
                    'display_name' => 'Edit Customer',
                    'description' => 'Can edit customer information'
                ],
                [
                    'name' => 'deactivate_customer',
                    'display_name' => 'Deactivate Customer',
                    'description' => 'Can deactivate a customer'
                ],
                [
                    'name' => 'delete_customer',
                    'display_name' => 'Delete Customer',
                    'description' => 'Can delete a customer'
                ],
                [
                    'name' => 'delete_customer_permanently',
                    'display_name' => 'Delete Customer Permanently',
                    'description' => 'Can delete a customer permanently'
                ],
                [
                    'name' => 'list_customers',
                    'display_name' => 'List Customers',
                    'description' => 'Can view list of customers'
                ]
            ],

            'order' => [
                [
                    'name' => 'create_order',
                    'display_name' => 'Create Order',
                    'description' => 'Can create an order'
                ],
                [
                    'name' => 'view_order',
                    'display_name' => 'View Order',
                    'description' => 'Can view order information'
                ],
                [
                    'name' => 'edit_order',
                    'display_name' => 'Edit Order',
                    'description' => 'Can edit order information'
                ],
                [
                    'name' => 'delete_order',
                    'display_name' => 'Delete Order',
                    'description' => 'Can delete an order'
                ],
                [
                    'name' => 'delete_order_permanently',
                    'display_name' => 'Delete Order Permanently',
                    'description' => 'Can delete an order permanently'
                ],
                [
                    'name' => 'list_orders',
                    'display_name' => 'List Orders',
                    'description' => 'Can view list of orders'
                ]
            ],

            'loyalty_offer' => [
                [
                    'name' => 'create_offer',
                    'display_name' => 'Create Loyalty Offer',
                    'description' => 'Can create a loyalty offer'
                ],
                [
                    'name' => 'edit_offer',
                    'display_name' => 'Edit Loyalty Offer',
                    'description' => 'Can edit loyalty offer details'
                ],
                [
                    'name' => 'delete_offer',
                    'display_name' => 'Delete Loyalty Offer',
                    'description' => 'Can delete a loyalty offer'
                ],
                [
                    'name' => 'list_offers',
                    'display_name' => 'List Loyalty Offers',
                    'description' => 'Can view list of loyalty offers'
                ]
            ],

            'special_discount' => [
                [
                    'name' => 'special_discount.create',
                    'display_name' => 'Create Special Discount',
                    'description' => 'Can create a special discount'
                ],
                [
                    'name' => 'special_discount.update',
                    'display_name' => 'Update Special Discount',
                    'description' => 'Can update a special discount'
                ],
                [
                    'name' => 'special_discount.list',
                    'display_name' => 'List Special Discounts',
                    'description' => 'Can view the list of special discounts'
                ]
            ],

            'general_settings' => [
                [
                    'name' => 'view_settings',
                    'display_name' => 'View General Settings',
                    'description' => 'Can view a general settings'
                ],
                [
                    'name' => 'edit_settings',
                    'display_name' => 'Edit General Settings',
                    'description' => 'Can edit general settings'
                ]
            ],
            'statistics_and_reports' => [
                [
                    'name' => 'view_statistics',
                    'display_name' => 'View General Statistics',
                    'description' => 'Can view general statistics based on their role'
                ]
            ],
            'roles_and_permissions' => [
                [
                    'name' => 'list_roles',
                    'display_name' => 'List Roles',
                    'description' => 'Can view list of roles'
                ]
            ],
            'transactions' => [
                [
                    'name' => 'list_transactions',
                    'display_name' => 'View Payment Transactions',
                    'description' => 'Can view the list of payment transactions',
                ],
                [
                    'name' => 'confirm_transaction',
                    'display_name' => 'Confirm Payment Transaction',
                    'description' => 'Can verify the status of a transaction and update the status if need be',
                ]
            ]
        ];
        foreach ($groupedPermissions as $g => $permissions){
            $group = \App\Models\PermissionGroup::firstOrCreate(['name' => $g]);
            foreach ($permissions as $p) {
                $p['group_id'] = $group->id;
                $permission = Permission::updateOrCreate([
                    'name' => $p['name']
                ], $p);
                if (!$overall_admin_role->hasPermission($permission->name)) {
                    $overall_admin_role->attachPermission($permission);
                };
            }
        }

        $devPermissions = [
            'roles_and_permissions' => [
                [
                    'name' => 'create_role',
                    'display_name' => 'Create A Role',
                    'description' => 'Can create a new role'
                ],
                [
                    'name' => 'edit_role',
                    'display_name' => 'Edit Any Role',
                    'description' => 'Can edit details of a role'
                ],
                [
                    'name' => 'delete_role',
                    'display_name' => 'Delete Role',
                    'description' => 'Can delete a role'
                ],
                [
                    'name' => 'list_roles',
                    'display_name' => 'List Roles',
                    'description' => 'Can view list of roles'
                ],
                [
                    'name' => 'create_permission',
                    'display_name' => 'Create A Permission',
                    'description' => 'Can create a new role'
                ],
                [
                    'name' => 'edit_permission',
                    'display_name' => 'Edit Permissions',
                    'description' => 'Can edit details of a role'
                ],
                [
                    'name' => 'delete_permission',
                    'display_name' => 'Delete Permission',
                    'description' => 'Can delete a permission'
                ],
                [
                    'name' => 'list_permissions',
                    'display_name' => 'List Permissions',
                    'description' => 'Can view list of permissions'
                ]
            ]
        ];
        foreach ($devPermissions as $g => $permissions){
            $group = \App\Models\PermissionGroup::firstOrCreate(['name' => $g]);
            foreach ($permissions as $p) {
                $p['group_id'] = $group->id;
                $permission = Permission::updateOrCreate([
                    'name' => $p['name']
                ], $p);
                if (!$dev_role->hasPermission($permission->name)) {
                    $dev_role->attachPermission($permission);
                };
            }
        }

        $dropoffAdminRole = Role::updateOrCreate([
            'name' => 'dropoff_admin'
        ],[
            'hierarchy' => 3,
            'display_name' => 'Dropoff Admin',
            'description' => 'Allowed to create dropoff orders'
        ]);

        $dropoffAdminPermissions = [
            'order' => [
                [
                    'name' => 'create_dropoff_order',
                    'display_name' => 'Create Dropoff Order',
                    'description' => 'Can create dropoff orders'
                ]
            ],
            'customer' => [
                [
                    'name' => 'create_customer',
                    'display_name' => 'Create Customer',
                    'description' => 'Can create a customer'
                ],
                [
                    'name' => 'single_customer_search',
                    'display_name' => 'Get Customer By Email or Phone',
                    'description' => 'Can search for a customer by exact email address or phone number'
                ]
            ],
        ];
        foreach ($dropoffAdminPermissions as $g => $permissions){
            $group = \App\Models\PermissionGroup::firstOrCreate(['name' => $g]);
            foreach ($permissions as $p) {
                $p['group_id'] = $group->id;
                $permission = Permission::updateOrCreate([
                    'name' => $p['name']
                ], $p);
                if (!$dropoffAdminRole->hasPermission($permission->name)) {
                    $dropoffAdminRole->attachPermission($permission);
                };
            }
        }
    }
}
