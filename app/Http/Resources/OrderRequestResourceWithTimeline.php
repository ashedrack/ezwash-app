<?php

namespace App\Http\Resources;

use App\Models\OrderRequest;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderRequestResourceWithTimeline extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $orderRequestTime = $this->delivery_request_time ? $this->delivery_request_time: $this->time;
        $orderReq = OrderRequest::find($this->id);
        $deliveryDate = $this->delivery_request_time ?? $this->time;
        return [
            'id' => $this->delivery_request_id ?? $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'order_id' => $this->order_id,
            'address' => !empty($this->address) ? (new UserAddressResource($this->address))->toArray($request) : null,
            'date' => Carbon::parse($orderRequestTime)->format('Y-m-d'),
            'time' => Carbon::parse($orderRequestTime)->format('H:i:s'),
            'note' => $this->note,
            'scheduled' => $this->scheduled,
            'timeline' => $orderReq->timelineForMobile(),
            'location' => $orderReq->store_location,
            'kwik_order_id' => $this->kwik_order_id,
            'amount' => $this->amount,
            'pickup_and_delivery' => $this->has_pickup,
            'order_request_type' => $orderReq->order_request_type->name,
            'created_at' => $this->delivery_request_created_at ?? $this->created_at,
            'delivery_date' => Carbon::parse($deliveryDate)->toDateTimeString(),
        ];
    }
}
