<?php

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use App\Models\OrdersService;
use App\Models\Order;
use App\Models\OrderType;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $godAdmin = getOverallAdmin('victoria@initsng.com');

        $locations = \App\Models\Location::all()->random(3);
        if(!empty($locations)) {
            foreach ($locations as $location) {
                $user = factory(\App\Models\User::class)->create([
                    'password' => bcrypt('customer_pass'),
                    'location_on_create' => $location->id,
                    'created_by' => $godAdmin->id,
                    'location_id' => $location->id,
                ]);
                $dispatcher = Order::getEventDispatcher();

                Order::unsetEventDispatcher();

                $this->createAnOrder([
                    'user_id' => $user->id,
                    'order_type' => OrderType::SELF_SERVICE,
                    'created_by' => $godAdmin->id,
                    'location_id' => $location->id,
                    'company_id' => $location->company_id,
                    'status' => \App\Classes\Meta::ORDER_STATUS_PENDING,
                    'payment_method' => PaymentMethod::CARD_PAYMENT,
                    'collected' => 0
                ]);

                $this->createAnOrder([
                    'user_id' => $user->id,
                    'order_type' => OrderType::SELF_SERVICE,
                    'created_by' => $godAdmin->id,
                    'location_id' => $location->id,
                    'company_id' => $location->company_id,
                    'status' => \App\Classes\Meta::ORDER_STATUS_COMPLETED,
                    'payment_method' => PaymentMethod::CASH_PAYMENT,
                    'collected' => 1
                ]);

                $this->createAnOrder([
                    'user_id' => $user->id,
                    'order_type' => OrderType::SELF_SERVICE,
                    'created_by' => $godAdmin->id,
                    'location_id' => $location->id,
                    'company_id' => $location->company_id,
                    'status' => \App\Classes\Meta::ORDER_STATUS_COMPLETED,
                    'payment_method' => PaymentMethod::POS_PAYMENT,
                    'collected' => 1
                ]);
                Order::setEventDispatcher($dispatcher);
            }
        }else{
            dump('Please run LocationsTableSeeder class first');
        }
    }
    function createAnOrder($orderDetails){
        $services = \App\Models\Service::all()->random(2);
        $qty = rand(1,4);
        $order = Order::create($orderDetails);
        foreach ($services as $s){
            OrdersService::create([
                'order_id' => $order->id,
                'service_id' => $s->id,
                'price' => $s->price,
                'quantity' => $qty
            ]);
        }
        $totalAmount = $order->order_services()->selectRaw('SUM(price * quantity) as total')->first()->total;
        $order->update([
            'amount' => $totalAmount
        ]);
    }
}

