<?php

namespace App\Models;

use App\Classes\Meta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LoyaltyOffer
 *
 * @property int $id
 * @property int $company_id
 * @property string $display_name
 * @property int $spending_requirement
 * @property int $discount_value
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property bool $status
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property string $prev_firebase_id
 * @property bool $is_special_offer
 *
 * @property \App\Models\Employee $employee
 * @property Collection|OrdersDiscount[] $orders_discounts
 * @property Collection|SpecialOfferCustomer[] $special_offer_customers
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class LoyaltyOffer extends Model
{
	use \Illuminate\Database\Eloquent\SoftDeletes;
//	public $incrementing = false;

	protected $casts = [
        'company_id' => 'int',
		'spending_requirement' => 'int',
		'discount_value' => 'int',
		'status' => 'bool',
		'created_by' => 'int',
		'is_special_offer' => 'bool'
	];

	protected $fillable = [
	    'company_id',
		'display_name',
		'spending_requirement',
		'discount_value',
		'start_date',
		'end_date',
		'status',
		'created_by',
        'prev_firebase_id',
        'is_special_offer'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class, 'created_by');
	}

	public function orders_discounts()
	{
		return $this->hasMany(\App\Models\OrdersDiscount::class);
	}

    public function users_discounts()
    {
        return $this->hasMany(UsersDiscount::class, 'offer_id');
    }

    public function appliedDiscounts()
    {
        return $this->hasMany(UsersDiscount::class, 'offer_id')->where('status', Meta::USED_DISCOUNT);
    }

    public function unusedDiscounts()
    {
        return $this->hasMany(UsersDiscount::class, 'offer_id')->where('status', Meta::UNUSED_DISCOUNT);
    }

	public function company()
    {
        return $this->belongsTo(Company::class);
    }

	public static function scopeGetAllowed($query, Employee $authUser)
    {
        if($authUser && !$authUser->company_id){
            return $query;
        }
        return $query->where('company_id', $authUser->company_id);
    }

    public function special_offer_customers()
    {
        return $this->hasMany(SpecialOfferCustomer::class);
    }
}
