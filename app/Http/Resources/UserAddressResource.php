<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
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
            'address' => $this->address,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'is_home_address' => $this->is_home_address == 1 ? True : False
        ];
    }
}
