<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount_spent' => $this->amount_spent,
            'discount_earned' => $this->discount_earned,
            'offer' => new LoyaltyOfferResource($this->offer)
        ];
    }
}
