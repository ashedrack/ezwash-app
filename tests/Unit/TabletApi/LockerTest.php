<?php

namespace Tests\Unit\TabletApi;

use App\Classes\Meta;
use App\Models\Location;
use App\Models\Locker;
use App\Models\Order;
use App\Models\OrdersLocker;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LockerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $authAdmin;
    protected $location;
    public function setUp():void
    {
        parent::setUp();

        $this->seed();

        Artisan::call('cache:clear');

        $this->withHeader('channel', 'tablet_app');
        $this->authAdmin = generateFakeEmployee([
            'email' => 'victoria@yahoo.com',
            'password' => bcrypt('password')
        ]);
        $this->location = Location::whereHas('orders')->first();
        $this->actingAsEmployee($this->authAdmin, $this->location->id );
    }
    /**
     * Route: /api/v2/employee/all-lockers
     * Test that all-location-lockers are returned successfully.
     *
     * @return void
     */
    public function testLocationLockersAreReturnedSuccessfully()
    {
        $url = '/api/v2/employee/all-lockers';
        $response = $this->get($url);
        $response->assertOk();

        $responseBody = $response->json();
        $this->assertTrue($responseBody['status']);

        $lockers = $responseBody['data']['lockers'];

        // Testing number_of_locker + 1 because the locker would include one with value of '0' to cater for out-of-locker.
        $this->assertEquals(($this->location->number_of_lockers + 1), count($lockers));
    }

    /**
     * Route: /api/v2/employee/update-order
     * Test that a pending dropoff order can be updated successfully
     */
    public function testUpdateDropoffOrderWithLockersSuccessfully()
    {
        $url = '/api/v2/employee/update-order';
        $location = $this->location;
        $user = User::all()->random(1)->first();
        $authAdmin = $this->authAdmin;
        if(empty($user)) {
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $location->id,
                'location_id' => $location->id,
            ]);
        }
        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => Meta::DROP_OFF_ORDER_TYPE,
            'bags' => 2,
            'note' => 'Some instruction',
            'created_by' => $authAdmin->id,
            'location_id' => $location->id,
            'company_id' => $location->company_id,
            'status' => Meta::ORDER_STATUS_PENDING,
            'collected' => 0
        ]);
        $lockers = $location->lockers()->where('occupied', 0)->get()->random(2)->pluck('locker_number');
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
            'lockers' => $lockers->toArray()

        ])->assertOk()
            ->assertJsonFragment(['payment_method' => 'card', 'status' => 'pending']);
        $unoccupied = $location->lockers()
            ->whereIn('locker_number', $lockers->toArray())
            ->where('occupied', 0)->count();
        $this->assertEquals(0, $unoccupied);
    }

    public function testCannotUpdateOrderWithOccupiedLockers()
    {
        $url = '/api/v2/employee/update-order';
        $location = $this->location;
        $user = User::all()->random(1)->first();
        $authAdmin = $this->authAdmin;
        if(empty($user)) {
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $location->id,
                'location_id' => $location->id,
            ]);
        }
        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => Meta::DROP_OFF_ORDER_TYPE,
            'bags' => 2,
            'note' => 'Some instruction',
            'created_by' => $authAdmin->id,
            'location_id' => $location->id,
            'company_id' => $location->company_id,
            'status' => Meta::ORDER_STATUS_PENDING,
            'collected' => 0
        ]);
        $lockers = $location->lockers()->where('occupied', 0)->get()->random(2);
        $order->lockers()->sync($lockers);
        Locker::whereIn('id', $lockers->pluck('id')->toArray())->update(['occupied' => 1]);

        $order2 = Order::create([
            'user_id' => $user->id,
            'order_type' => Meta::DROP_OFF_ORDER_TYPE,
            'bags' => 2,
            'note' => 'Some instruction',
            'created_by' => $authAdmin->id,
            'location_id' => $location->id,
            'company_id' => $location->company_id,
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
                    'quantity' => 2,
                ]
            ],
            'payment_method' => 'card',
            'lockers' => $lockers->pluck('locker_number')->toArray()

        ])->assertStatus(400);
    }

    /**
     * Route: /api/v2/employee/collect-order
     * Test that only completed orders can be marked as collected
     */
    public function testOnlyCompletedOrderCanBeMarkedAsCollected()
    {
        $url = '/api/v2/employee/collect-order';
        $location = $this->location;
        $user = User::all()->random(1)->first();
        $authAdmin = $this->authAdmin;
        if(empty($user)) {
            $user = factory(User::class)->create([
                'password' => bcrypt('password'),
                'created_by' => $authAdmin->id,
                'location_on_create' => $location->id,
                'location_id' => $location->id,
            ]);
        }
        $order = Order::create([
            'user_id' => $user->id,
            'order_type' => Meta::DROP_OFF_ORDER_TYPE,
            'bags' => 2,
            'note' => 'Some instruction',
            'created_by' => $authAdmin->id,
            'location_id' => $location->id,
            'company_id' => $location->company_id,
            'status' => Meta::ORDER_STATUS_PENDING,
            'collected' => 0
        ]);
        $lockers = $location->lockers()->where('occupied', 0)->get()->random(2);
        $order->lockers()->sync($lockers);
        Locker::whereIn('id', $lockers->pluck('id')->toArray())->update(['occupied' => 1]);

        $this->post($url, [
            'order_id' => $order->id
        ])->assertStatus(400);
    }

    /**
     * Route: /api/v2/employee/collect-order
     * Test that lockers are released when an order with lockers is marked as collected
     */
    public function testLockersAreReleasedWhenOrderIsCollected()
    {
        try {
            $url = '/api/v2/employee/collect-order';
            $location = $this->location;
            $user = User::all()->random(1)->first();
            $authAdmin = $this->authAdmin;
            if (empty($user)) {
                $user = factory(User::class)->create([
                    'password' => bcrypt('password'),
                    'created_by' => $authAdmin->id,
                    'location_on_create' => $location->id,
                    'location_id' => $location->id,
                ]);
            }
            $order = Order::create([
                'user_id' => $user->id,
                'order_type' => Meta::DROP_OFF_ORDER_TYPE,
                'bags' => 2,
                'note' => 'Some instruction',
                'created_by' => $authAdmin->id,
                'location_id' => $location->id,
                'company_id' => $location->company_id,
                'status' => Meta::ORDER_STATUS_PENDING,
                'collected' => 0
            ]);
            $lockers = $location->lockers()->where('occupied', 0)->get()->random(2);
            $order->lockers()->sync($lockers);
            Locker::whereIn('id', $lockers->pluck('id')->toArray())->update(['occupied' => 1]);
            $order->update([
                'status' => Meta::ORDER_STATUS_COMPLETED
            ]);
            $response = $this->post($url, [
                'order_id' => $order->id
            ]);
            $response->assertOk();

            $count = Locker::whereIn('id', $lockers->pluck('id')->toArray())->where('occupied', 1)->count();
            $this->assertEquals(0, $count);
        } catch (\Exception $exception){
            logCriticalError('ERRR', $exception);
            throw $exception;
        }

    }
}
