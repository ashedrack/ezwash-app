<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAddress
 * @property int $user_id
 * @property string $address
 * @property float $longitude
 * @property float $latitude
 * @property int $is_home_address
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @package App\Models
 * @mixin \Eloquent
 */
class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'longitude',
        'latitude',
        'is_home_address'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
