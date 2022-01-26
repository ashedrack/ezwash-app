<?php

namespace App\Models;

use App\Traits\CoreModelMethods;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Company
 *
 * @property int $id
 * @property string $name
 * @property int $owner_id
 * @property string $description
 * @property int $is_active
 * @property string $ps_live_secret
 * @property string $ps_test_secret
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 *
 * @property \App\Models\Employee $employee
 * @property \Illuminate\Database\Eloquent\Collection $employees
 * @property \Illuminate\Database\Eloquent\Collection $locations
 * @property \Illuminate\Database\Eloquent\Collection $locations_revs
 * @property \Illuminate\Database\Eloquent\Collection $orders
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Company extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use CoreModelMethods;

    const EZWASH_MAIN = 1;

    protected $casts = [
        'owner_id' => 'int',
        'is_active' => 'int'
    ];

    protected $hidden = [
        'ps_live_secret',
        'ps_test_secret'
    ];

    protected $fillable = [
        'name',
        'owner_id',
        'description',
        'is_active',
        'ps_live_secret',
        'ps_test_secret'
    ];

    public function owner()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'owner_id')->withoutGlobalScopes();
    }

    public function employees()
    {
        return $this->hasMany(\App\Models\Employee::class);
    }

    public function locations()
    {
        return $this->hasMany(\App\Models\Location::class);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function loyaltyOffers()
    {
        return $this->hasMany(LoyaltyOffer::class);
    }
}
