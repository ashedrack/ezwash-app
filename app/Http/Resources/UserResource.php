<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected $user;

    public function __construct(User $resource) {

        $this->user = $resource;
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $userDiscount = $this->user->unusedDiscount()->with(['offer'])->first();
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'gender' => $this->user->gender,
            'profile_picture' => $this->user->avatar,
            'is_active' => ($this->user->is_active == 0) ? false: true,
//            'location' => $this->user->location_id !== null ? $this->user->location->name : null,
            'created_at' => $this->user->created_at->toDateTimeString(),
            'updated_at' => $this->user->updated_at->toDateTimeString(),
            'user_discount' => (!empty($userDiscount)) ? new UserDiscountResource($userDiscount) : null,
            'home_address' => (!empty($this->user->userHomeAddress)) ? new UserAddressResource($this->user->userHomeAddress) : null
        ];
    }
}
