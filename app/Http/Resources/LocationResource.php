<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'store_image' => $this->store_image,
            'number_of_lockers' => $this->number_of_lockers,
            'company_id' => $this->company_id,
            'is_active' => $this->is_active,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
        ];
    }
}
