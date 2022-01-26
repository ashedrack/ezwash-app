<?php

namespace App\Http\Resources;

use App\Classes\Meta;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @var Order $order;
     */
    protected $order;
    public function __construct(Order $order) {
        parent::__construct($order);
        $this->order = $order;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lockers = [];
        if($this->order_type === Meta::DROP_OFF_ORDER_TYPE && !empty($this->order->locker_numbers)){
        	$lockers = json_decode($this->order->locker_numbers, true);
        }
        $pickupCost = $this->order->hasPickupRequest() ? $this->order->pickupRequest()->amount : null;
        $deliveryCost = ($this->order->hasDeliveryRequest(true)) ? $this->order->deliveryRequest()->amount : null;
        $hasDiscount = $this->order->user->discounts()->exists();
        return [
            'id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'order_type' => $this->order->orderType->name,
            'services' => OrderServiceResource::collection($this->order->order_services)->toArray($request),
            'status' => $this->order->order_status->name,
            'amount_before_discount' => $this->order->amount_before_discount, // Same as sub_total, leaving this here to prevent breaking previous implementations
            'sub_total' => $this->order->amount_before_discount,
            'discount' => $hasDiscount ? $this->order->discountApplied() : null,
            'pickup_fee' => $pickupCost,
            'delivery_fee' => $deliveryCost,
            'grand_total' => $this->order->getAmountToPay(),
            'amount' => $this->order->getAmountToPay(), // Same as grand_total, leaving this here to prevent breaking previous implementations
            'payment_method' => ($this->order->paymentMethod) ? $this->order->paymentMethod->name : null,
            'collected' => ($this->order->collected == 1),
            'note' => $this->order->note,
            'bags' => $this->order->bags,
            'lockers' => $lockers,
            'created_at' => $this->order->created_at->toDateTimeString()
        ];
    }
}
