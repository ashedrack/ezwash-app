<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrdersDiscount
 * 
 * @property int $id
 * @property int $order_id
 * @property int $users_discount_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * 
 * @property \App\Models\Order $order
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class OrdersDiscount extends Model
{
	use \Illuminate\Database\Eloquent\SoftDeletes;

	protected $casts = [
		'order_id' => 'int',
		'users_discount_id' => 'int',
        'loyalty_offer_id' => 'int'
	];

	protected $fillable = [
		'order_id',
		'users_discount_id',
        'loyalty_offer_id'
	];

	public function order()
	{
		return $this->belongsTo(\App\Models\Order::class);
	}

	public function users_discount()
    {
        return $this->belongsTo(UsersDiscount::class);
    }

    public function offer()
    {
        return $this->belongsTo(LoyaltyOffer::class);
    }
}
