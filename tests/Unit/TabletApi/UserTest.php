<?php

namespace Tests\Unit\TabletApi;

use App\Classes\Meta;
use App\Models\Company;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrdersService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $authAdmin;
    protected $company;
    protected $location;

    public function setUp():void
    {
        parent::setUp();
        $this->withHeader('channel', 'tablet_app');
        $this->seed();
        $this->authAdmin = generateFakeEmployee(['email' => 'victoria@yahoo.com', 'password' => bcrypt('password')]);
        $this->location = Location::whereHas('orders')->first();
        Artisan::call('cache:clear');
    }

    /**
     * 7. Test that user creation without required fields
     *
     * @throws \Exception
     */
    public function testUserCreationFailsWithoutValidToken()
    {
        $user = factory(User::class)->make();
        $response = $this->post('/api/v2/employee/create-customer', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender
        ]);

        $response2 = $this->post('/api/v2/employee/create-customer', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'token' => random_bytes(32)
        ]);

        $response->assertForbidden()
            ->assertJson(['status' => false, 'message' => 'Token not Provided'] );

        $response2->assertUnauthorized()
            ->assertJson(['status' => false, 'message' => 'Token invalid']);

    }

    /**
     * 8. Test that request will not be successful when store_location is not set
     */
    public function testUserCreationFailsWithoutStoreLocation()
    {
        $response = $this->post('/api/v2/employee/login', [
            'email' => $this->authAdmin->email,
            'password' => 'password'
        ]);
        $response->assertOk()
            ->assertJsonFragment(['status' => true]);

        $responseData = $response->json();
        $accessToken = $responseData['data']['access_token'];

        $user = factory(User::class)->make();
        $response2 = $this->post('/api/v2/employee/create-customer', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'token' => $accessToken
        ]);
        $response2->assertStatus(400)
            ->assertJsonFragment(['message' => 'store_location not specified']);
    }

    /**
     * 9. Ensure that request is successful if a valid token is set
     */
    public function testUserCreationPassesWithValidToken()
    {
        $this->actingAsEmployee($this->authAdmin, $this->location->id );
        $user = factory(User::class)->make();
        $response = $this->post('/api/v2/employee/create-customer', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender
        ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => true]);
    }

    /**
     * 10.
     */
    public function testUserSearchRequiresQueryString()
    {
        $location = $this->location;
        factory(User::class, 2)->create([
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);
        $this->actingAsEmployee($this->authAdmin, $location->id);

        $response = $this->post('/api/v2/employee/search-customers', [
            'records_per_page' => 10,
            'page' => 2
        ]);
        $response->assertStatus(400)
            ->assertSeeText('The query string field is required');
    }

    /**
     * 11.
     */
    public function testUserSearchFiltersByNameAndEmail()
    {
        $location = $this->location;
        factory(User::class)->create([
            'name' => 'Victor Ademide',
            'email' => 'vicsimbi@adbul.ng',
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);
        factory(User::class)->create([
            'name' => 'Simbi Abdul',
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);

        factory(User::class, 5)->create([
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ])->toArray();

        $this->actingAsEmployee($this->authAdmin, $location->id);

        $response = $this->post('/api/v2/employee/search-customers', [
            'query_string' => 'simbi'
        ]);
        $response->assertOk()
            ->assertJsonFragment(['status' => true])
            ->assertSeeText('Simbi Abdul')
            ->assertSeeText('vicsimbi@adbul.ng');
    }

    public function testCanGetUsersOrdersSuccessfully()
    {
        $location = $this->location;
        $user = factory(User::class)->create([
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);
        $this->actingAsEmployee($this->authAdmin, $location->id);
        $response = $this->post('/api/v2/employee/get-customer-orders', [
            'customer_id' => $user->id
        ]);
        $responseBody = $response->json();
        $response->assertOk();
        $responseData = $responseBody['data'];
        $this->assertEquals(0, count($responseData['orders']));

        $order  = Order::create([
            'user_id' => $user->id,
            'order_type' => Meta::SELF_SERVICE_ORDER_TYPE,
            'created_by' => $this->authAdmin->id,
            'location_id' => $location->id,
            'company_id' => $this->location->company_id,
            'status' => Meta::ORDER_STATUS_COMPLETED,
            'payment_method' => rand(1,3),
            'collected' => 1
        ]);

        $services = Service::all()->random(3);
        foreach ($services as $s){
            OrdersService::create([
                'order_id' => $order->id,
                'service_id' => $s->id,
                'price' => $s->price,
                'quantity' => rand(1, 5)
            ]);
        }

        $response = $this->post('/api/v2/employee/get-customer-orders', [
            'customer_id' => $user->id
        ]);
        $response->assertOk()
            ->assertJsonFragment(['status' => true])
            ->assertJsonStructure([
                'message','data','status'
            ]);
    }

    /**
     * Route: /api/v2/employee/find-customer
     */
    public function testDropoffAdminCanFindCustomerByEmail()
    {
        $location = $this->location;
        $employee = generateFakeEmployee([
            'email' => 'victoria@yahoo.com',
            'password' => bcrypt('password'),
            'role' => [
                'name' => 'dropoff_admin',
                'hierarchy' => 3,
                'display_name' => 'Dropoff Admin',
                'description' => 'Allowed to create dropoff orders'
            ],
            'company_id' => $location->company_id
        ]);
        $this->actingAsEmployee($employee, $this->location->id );
        factory(User::class)->create([
            'name' => 'Victor Ademide',
            'email' => 'vicsimbi@adbul.ng',
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);
        factory(User::class)->create([
            'name' => 'Simbi Abdul',
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);

        factory(User::class, 5)->create([
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ])->toArray();

        $this->actingAsEmployee($this->authAdmin, $location->id);

        $response = $this->post('/api/v2/employee/find-customer', [
            'query_string' => 'vicsimbi@adbul.ng'
        ]);
        $response->assertOk()
            ->assertJsonFragment(['status' => true, 'name' => 'Victor Ademide']);
    }

    /**
     * Route: /api/v2/employee/find-customer
     */
    public function testDropoffAdminCanFindCustomerByPhone()
    {
        $location = $this->location;
        $user = factory(User::class)->create([
            'name' => 'Simbi Abdul',
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ]);

        factory(User::class, 5)->create([
            'created_by' => $this->authAdmin->id,
            'location_on_create' => $location->id,
            'location_id' => $location->id
        ])->toArray();
        $employee = generateFakeEmployee([
            'email' => 'victoria@yahoo.com',
            'password' => bcrypt('password'),
            'role' => [
                'name' => 'dropoff_admin',
                'hierarchy' => 3,
                'display_name' => 'Dropoff Admin',
                'description' => 'Allowed to create dropoff orders'
            ],
            'company_id' => $location->company_id
        ]);
        $this->actingAsEmployee($employee, $this->location->id );
        $phone = '0'. substr(trim($user->phone), -10);
        $response = $this->post('/api/v2/employee/find-customer', [
            'query_string' => $phone
        ]);
        $response->assertOk()
            ->assertJsonFragment(['status' => true, 'name' => 'Simbi Abdul']);
    }

}
