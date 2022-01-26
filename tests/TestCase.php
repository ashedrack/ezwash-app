<?php

namespace Tests;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrdersService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAsEmployee(Employee $employee, $storeLocation)
    {
        $accessToken = auth('admins')->attempt(['email' => $employee->email, 'password' => 'password']);
        $this->withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'store_location' => $storeLocation
        ]);
    }

    public function createAnOrder($orderDetails){
        $services = \App\Models\Service::all()->random(2);
        $order = Order::create($orderDetails);
        foreach ($services as $s){
            OrdersService::create([
                'order_id' => $order->id,
                'service_id' => $s->id,
                'price' => $s->price,
                'quantity' => rand(1,4)
            ]);
        }
        $totalAmount = $order->order_services()->selectRaw('SUM(price * quantity) as total')->first()->total;
        $order->update([
            'amount' => $totalAmount
        ]);
    }
}
