<?php

namespace App\Observers;

use App\Models\OrderRequest;
use App\Models\OrderRequestType;

class OrderRequestObserver
{
    /**
     * Handle the order request "created" event.
     *
     * @param  \App\Models\OrderRequest  $orderRequest
     * @return void
     */
    public function created(OrderRequest $orderRequest)
    {
        if($orderRequest->order_request_type_id === OrderRequestType::DELIVERY
            && $orderRequest->has_pickup){
            OrderRequest::where('order_id', $orderRequest->order_id)
                ->where('order_request_type_id', OrderRequestType::PICKUP)
                ->update(['has_delivery' => true]);
        }
    }
}
