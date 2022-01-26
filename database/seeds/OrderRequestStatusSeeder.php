<?php

use Illuminate\Database\Seeder;
use App\Models\OrderRequestStatus;

class OrderRequestStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //in progress, picked-up, canceled, delivered
        $orderRequestStatuses = [
            [
                'id' => OrderRequestStatus::PICKUP_REQUESTED,
                'description' => 'Customer has initiated a pickup request',
                'display_name' => 'Pickup Requested',
                'name' => 'pickup_requested'
            ],
            [
                'id' => OrderRequestStatus::PICKUP_CANCELED,
                'description' => 'Pickup request was canceled before a dispatch rider was assigned',
                'display_name' => 'Pickup Canceled',
                'name' => 'pickup_canceled',
            ],
            [
                'id' => OrderRequestStatus::PICKUP_STARTED,
                'description' => 'A dispatch rider has been assigned to the request',
                'display_name' => 'Pickup Assigned',
                'name' => 'pickup_started'
            ],
            [
                'id' => OrderRequestStatus::PICKED_UP,
                'description' => 'Order Picked up',
                'display_name' => 'Order Picked up',
                'name' => 'picked_up'
            ],
            [
                'id' => OrderRequestStatus::DROPPED_OFF,
                'description' => 'Order has been dropped off  at specified ezwash location',
                'display_name' => 'Dropped Off',
                'name' => 'dropped_off'
            ],
            [
                'id' => OrderRequestStatus::WASH_IN_PROGRESS,
                'description' => 'Order is being processed',
                'display_name' => 'In progress',
                'name' => 'in_progress'
            ],
            [
                'id' => OrderRequestStatus::READY_FOR_COLLECTION,
                'description' => 'Order is ready for collection',
                'display_name' => 'Ready for collection',
                'name' => 'ready_for_collection'
            ],
            [
                'id' => OrderRequestStatus::DELIVERY_REQUESTED,
                'description' => 'Customer initiated a delivery request',
                'display_name' => 'Delivery Requested',
                'name' => 'delivery_requested'
            ],
            [
                'id' => OrderRequestStatus::DELIVERY_CANCELLED,
                'description' => 'Delivery request was canceled before a dispatch rider was assigned',
                'display_name' => 'Delivery Canceled',
                'name' => 'delivery_canceled'
            ],
            [
                'id' => OrderRequestStatus::DELIVERY_STARTED,
                'description' => 'A dispatch rider has been assigned to the request',
                'display_name' => 'Delivery Assigned',
                'name' => 'delivery_started'
            ],
            [
                'id' => OrderRequestStatus::PICKED_UP_FOR_DELIVERY,
                'description' => 'Order has been picked up for delivery',
                'display_name' => 'Delivery In Progress',
                'name' => 'pickedup_for_delivery'
            ],
            [
                'id' => OrderRequestStatus::ORDER_DELIVERED,
                'description' => 'Order reached the customer',
                'display_name' => 'Delivered',
                'name' => 'delivered'
            ],
            [
                'id' => OrderRequestStatus::REQUEST_FAILED,
                'description' => 'Request Failed',
                'display_name' => 'Request',
                'name' => 'request_failed'
            ],
            [
                'id' => OrderRequestStatus::DELIVERY_MANUALLY_SORTED,
                'description' => 'Delivery manually fixed when payment were not being processed for the delivery',
                'display_name' => 'Delivery Manually Resolved',
                'name' => 'delivery_manually_resolved'
            ]

        ];
        foreach ($orderRequestStatuses as $s){
            OrderRequestStatus::updateOrCreate(
                ['id' => $s['id'], 'name' => $s['name']], $s
            );
        }
    }
}
