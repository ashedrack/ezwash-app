<?php

namespace Tests\Unit\TabletApi;

use App\Classes\Meta;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrdersService;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\User;
use function GuzzleHttp\Promise\queue;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $authAdmin;

    /**
     * @var Location $location
     */
    protected $location;
    public function setUp():void
    {
        parent::setUp();
        $this->withHeader('channel', 'tablet_app');
        $this->seed();
        $this->authAdmin = generateFakeEmployee(['email' => 'victoria@yahoo.com', 'password' => bcrypt('password')]);
        $this->location = Location::whereHas('orders')->first();
        $this->actingAsEmployee($this->authAdmin, $this->location->id );
        Artisan::call('cache:clear');
    }
    /**
     * Route: /api/v2/employee/get-single-order
     * Tests that the route validation works properly
     *
     * @return void
     */
    public function testSingleOrderRouteValidationWorks()
    {
        $request = $this->post('/api/v2/employee/get-single-order');
        $request->assertStatus(400)
            ->assertSeeText('customer id field is required')
            ->assertDontSeeText('order id field is required');
        $authAdmin = $this->authAdmin;
        $user = factory(User::class)->create([
            'password' => bcrypt('password'),
            'created_by' => $authAdmin->id,
            'location_on_create' => $this->location->id,
            'location_id' => $this->location->id,
        ]);
        $this->post('/api/v2/employee/get-single-order', [
            'customer_id' => $user->id,
            'order_id' => 1
        ])->assertStatus(400)
            ->assertSeeText('order id is invalid');
    }
    /**
     * Route: /api/v2/employee/get-order
     * Test the route works with valid request data
     * @return void
     */
    public function testCanFetchSingleOrder()
    {
        $authAdmin = $this->authAdmin;
        $user = $this->location->users()->whereHas('orders')->first();
        if(empty($user)){
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $this->location->id,
                'location_id' => $this->location->id,
            ]);
            $this->createAnOrder([
                'user_id' => $user->id,
                'order_type' => Meta::SELF_SERVICE_ORDER_TYPE,
                'created_by' => $authAdmin->id,
                'location_id' => $this->location->id,
                'company_id' => $this->location->company_id,
                'status' => Meta::ORDER_STATUS_PENDING,
                'payment_method' => PaymentMethod::CARD_PAYMENT,
                'collected' => 0
            ]);
        }
        $order = $user->orders()->first();
        $response = $this->post('/api/v2/employee/get-single-order', [
            'customer_id' => $user->id,
            'order_id' => $order->id
        ]);
        $response->assertOk()
            ->assertJsonFragment(['status' => true]);
    }

    /**
     * Route: api/employee/get-all-services
     */
    public function testCanRetrieveAllService()
    {
        $response = $this->post('/api/v2/employee/get-all-services');

        $response->assertOk()
            ->assertJsonFragment(['status' => true]);

        $responseBody = $response->json();
        $responseData = $responseBody['data'];
        $totalServices = Service::count();
        $this->assertEquals($totalServices, count($responseData['services']));
    }

    /**
     * Route: api/employee/create-selfservice-order
     */
    public function testProperValidationInCreateSelfServiceOrderRoute()
    {
        $user = $this->location->users()->first();
        if(empty($user)) {
            $authAdmin = $this->authAdmin;
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $this->location->id,
                'location_id' => $this->location->id,
            ]);
        }
        $this->post('/api/v2/employee/create-selfservice-order')
            ->assertStatus(400)
            ->assertSeeText('customer id field is required')
            ->assertDontSeeText('services field is required');

        $this->post('/api/v2/employee/create-selfservice-order', [
            'customer_id' => $user->id,
            'services' => 3
        ])->assertStatus(400)
            ->assertSeeText('services must be an array');

        $this->post('/api/v2/employee/create-selfservice-order', [
            'customer_id' => $user->id,
            'services' => [
                [
                    'id' => 2,
                    'quantity' => 3
                ],
                [
                    'id' => 1
                ]
            ],
            'payment_method' => 'cash'
        ])->assertStatus(400)
            ->assertSeeTextInOrder(['services', 'quantity field is required']);
    }

    /**
     * Route: api/employee/create-selfservice-order
     */
    public function testCanCreateSelfServiceOrderSuccessfully()
    {
        $user = $this->location->users()->first();
        if(empty($user)) {
            $authAdmin = $this->authAdmin;
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $this->location->id,
                'location_id' => $this->location->id,
            ]);
        }
        $this->post('/api/v2/employee/create-selfservice-order', [
            'customer_id' => $user->id,
            'services' => [
                [
                    'id' => 2,
                    'quantity' => 3
                ],
                [
                    'id' => 1,
                    'quantity' => 3,
                ]
            ],
            'payment_method' => 'card'
        ])->assertOk()
            ->assertJsonFragment(['order_type' => 'self_service', 'payment_method' => 'card', 'status' => 'pending']);
    }

    /**
     * Route: api/employee/update-order
     * Test proper validation is implemented
     */
    public function testProperValidationInUpdateOrderRoute()
    {
        $url = '/api/v2/employee/update-order';
        $user = $this->location->users()->whereHas('orders', function ($q) {
            $q->where('status', Meta::ORDER_STATUS_PENDING);
        })->first();
        $authAdmin = $this->authAdmin;
        if (empty($user)) {
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $this->location->id,
                'location_id' => $this->location->id,
            ]);
            $this->createAnOrder([
                'user_id' => $user->id,
                'order_type' => Meta::SELF_SERVICE_ORDER_TYPE,
                'created_by' => $authAdmin->id,
                'location_id' => $this->location->id,
                'company_id' => $this->location->company_id,
                'status' => Meta::ORDER_STATUS_PENDING,
                'payment_method' => PaymentMethod::CARD_PAYMENT,
                'collected' => 0
            ]);
        }
        $this->createAnOrder([
            'user_id' => $user->id,
            'order_type' => Meta::SELF_SERVICE_ORDER_TYPE,
            'created_by' => $authAdmin->id,
            'location_id' => $this->location->id,
            'company_id' => $this->location->company_id,
            'status' => Meta::ORDER_STATUS_COMPLETED,
            'payment_method' => PaymentMethod::CASH_PAYMENT,
            'collected' => 1
        ]);
        $order = $user->orders()->where('status', Meta::ORDER_STATUS_PENDING)->first();
        $this->post($url)
            ->assertStatus(400);

        $this->post($url, [
            'order_id' => $order->id,
            'services' => 3
        ])->assertStatus(400)
            ->assertSeeText('services must be an array');

        $completedOrder = $user->orders()->where('status', Meta::ORDER_STATUS_COMPLETED)->first();
        $this->post($url, [
            'order_id' => $completedOrder->id,
            'services' => [
                [
                    'id' => 2,
                    'quantity' => 3
                ],
                [
                    'id' => 1,
                    'quantity' => 3
                ]
            ],
            'payment_method' => 'card'
        ])->assertStatus(400)
            ->assertSeeTextInOrder(['Cannot update a completed order']);
    }

    /**
 * Route: api/employee/update-order
 * Test that a pending order can be updated successfully
 */
    public function testCanUpdateASelfserviceOrderSuccessfully()
    {
        $url = '/api/v2/employee/update-order';
        $user = $this->location->users()->first();
        if(empty($user)) {
            $authAdmin = $this->authAdmin;
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $this->location->id,
                'location_id' => $this->location->id,
            ]);
            $this->createAnOrder([
                'user_id' => $user->id,
                'order_type' => Meta::SELF_SERVICE_ORDER_TYPE,
                'created_by' => $authAdmin->id,
                'location_id' => $this->location->id,
                'company_id' => $this->location->company_id,
                'status' => Meta::ORDER_STATUS_PENDING,
                'payment_method' => PaymentMethod::CARD_PAYMENT,
                'collected' => 0
            ]);
        }
        $order = $user->orders()->where('status', Meta::ORDER_STATUS_PENDING)->first();
        $this->post($url, [
            'order_id' => $order->id,
            'services' => [
                [
                    'id' => 2,
                    'quantity' => 3
                ],
                [
                    'id' => 1,
                    'quantity' => 2,
                ]
            ],
            'payment_method' => 'card'
        ])->assertOk()
            ->assertJsonFragment(['payment_method' => 'card', 'status' => 'pending']);

        $order = $user->orders()->where('status', Meta::ORDER_STATUS_PENDING)->first();
        $this->post($url, [
            'order_id' => $order->id,
            'services' => [
                [
                    'id' => 2,
                    'quantity' => 3
                ],
                [
                    'id' => 1,
                    'quantity' => 3,
                ]
            ],
            'payment_method' => 'cash'
        ])->assertOk()
            ->assertJsonFragment(['payment_method' => 'cash', 'status' => 'completed']);
    }

    /**
     * Route: api/employee/update-order
     * Test that a pending dropoff order can be updated successfully
     */
    public function testCanUpdateADropoffOrderSuccessfully()
    {
        $url = '/api/v2/employee/update-order';
        $user = User::all()->random(1)->first();
        $authAdmin = $this->authAdmin;
        if(empty($user)) {
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $this->location->id,
                'location_id' => $this->location->id,
            ]);
        }
        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => Meta::DROP_OFF_ORDER_TYPE,
            'bags' => 2,
            'note' => 'Some instruction',
            'created_by' => $authAdmin->id,
            'location_id' => $this->location->id,
            'company_id' => $this->location->company_id,
            'status' => Meta::ORDER_STATUS_PENDING,
            'collected' => 0
        ]);
        $allLocationLockers = $this->location->lockers()->where('occupied', false)->get();

        $lockers = $allLocationLockers->random(2)->pluck('locker_number')->toArray();
        $this->post($url, [
            'order_id' => $order->id,
            'services' => [
                [
                    'id' => 2,
                    'quantity' => 3
                ],
                [
                    'id' => 1,
                    'quantity' => 2,
                ]
            ],
            'payment_method' => 'card',
            'lockers' => $lockers
        ])->assertOk()
            ->assertJsonFragment(['payment_method' => 'card', 'status' => 'pending']);

        $lockers2 = $allLocationLockers->whereNotIn('locker_number', $lockers)->random(1)->pluck('locker_number')->toArray();
        $order2 = Order::create([
            'user_id' => $user->id,
            'order_type' => Meta::DROP_OFF_ORDER_TYPE,
            'bags' => 1,
            'note' => 'Some instruction',
            'created_by' => $authAdmin->id,
            'location_id' => $this->location->id,
            'company_id' => $this->location->company_id,
            'status' => Meta::ORDER_STATUS_PENDING,
            'collected' => 0
        ]);
        $this->post($url, [
            'order_id' => $order2->id,
            'services' => [
                [
                    'id' => 2,
                    'quantity' => 3
                ],
                [
                    'id' => 1,
                    'quantity' => 3,
                ]
            ],
            'payment_method' => 'cash',
            'lockers' => $lockers2
        ])->assertOk()
            ->assertJsonFragment(['payment_method' => 'cash', 'status' => 'completed']);
    }
}
