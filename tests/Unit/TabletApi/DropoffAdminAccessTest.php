<?php

namespace Tests\Unit\TabletApi;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DropoffAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Employee|Model|null $authAdmin
     */
    protected $authAdmin;

    /**
     * @var Location|Model|null $location
     */
    protected $location;

    public function setUp(): void
    {
        parent::setUp();
        $this->withHeader('channel', 'tablet_app');
        $this->seed();
        $companies = Company::has('locations', '>', 0)->has('orders', '>', 0)->get();
        if($companies->isNotEmpty()){
            $company = $companies->random(1)->first();
        } else {
            dump($companies->toArray());
            throw new \Exception('Companies with locations and orders not found');
        }
        $this->authAdmin = generateFakeEmployee([
            'email' => 'victoria@yahoo.com',
            'password' => bcrypt('password'),
            'role' => [
                'name' => 'dropoff_admin',
                'hierarchy' => 3,
                'display_name' => 'Dropoff Admin',
                'description' => 'Allowed to create dropoff orders'
            ],
            'company_id' => $company->id
        ]);
        $this->location = Location::where('company_id', $company->id)->has('orders', '>', 0)->first();
        $this->actingAsEmployee($this->authAdmin, $this->location->id);
        Artisan::call('cache:clear');
    }

    /**
     * Route: /api/v2/employee/create-customer
     * Test that employee with dropoff_admin role can create dropoff order
     *
     * @return void
     */
    public function testDropoffAdminCanCreateCustomer()
    {
        $url = '/api/v2/employee/create-customer';
        $user = factory(User::class)->make();
        $this->post($url, [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender
        ])->assertOk()->assertJsonFragment(['status' => true]);
    }

    /**
     * Route: /api/v2/employee/create-dropoff-order
     * Test that employee without dropoff_admin role cannot create dropoff order
     *
     * @return void
     */
    public function testCannotCreateDropoffOrder()
    {
        $url = '/api/v2/employee/create-dropoff-order';
        $location = $this->location;
        $employee = factory(Employee::class)->create([
            'company_id' => $location->company_id,
            'location_id' => $location->id,
            'is_active' => 1,
            'email_verified_at' => now()
        ]);
        $this->actingAsEmployee($employee, $location->id);
        $user = factory(User::class)->create([
            'created_by' => $employee->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);
        $this->post($url, [
            'customer_id' => $user->id,
            'bags' => 4,
            'notes' => 'test note'
        ])->assertStatus(403);
    }

    /**
     * Route: /api/v2/employee/create-dropoff-order
     * Test that employee with dropoff_admin role can create dropoff order
     *
     * @return void
     */
    public function testDropoffAdminCanCreateDropoffOrder()
    {
        $url = '/api/v2/employee/create-dropoff-order';
        $location = $this->location;
        $user = factory(User::class)->create([
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);
        $this->post($url, [
            'customer_id' => $user->id,
            'bags' => 4,
            'notes' => 'Test note'
        ])->assertOk()->assertJsonFragment(['status' => true]);
    }
}
