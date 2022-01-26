<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCardResource extends JsonResource
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
            'last_four' => $this->last_four,
            'card_type' => $this->card_type,
            'exp_month' => $this->exp_month,
            'exp_year' => $this->exp_year,
            'bank' => $this->bank,
        ];
    }
}
