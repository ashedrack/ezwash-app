<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LockerResource extends JsonResource
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
            'locker_number' => $this->locker_number,
            'occupied' => ($this->locker_number !== 0 && $this->occupied === 1) ? true: false
        ];
    }
}
