<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 03 Dec 2019 13:37:41 +0100.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UsersDiscount
 * 
 * @property int $id
 * @property int $user_id
 * @property int $offer_id
 * @property float $amount_spent
 * @property float $discount_earned
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class UsersDiscount extends Model
{
	protected $casts = [
		'user_id' => 'int',
		'offer_id' => 'int',
		'amount_spent' => 'float',
		'discount_earned' => 'float',
		'status' => 'int'
	];

	protected $fillable = [
		'user_id',
		'offer_id',
		'amount_spent',
		'discount_earned',
		'status'
	];

	public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offer()
    {
        return $this->belongsTo(LoyaltyOffer::class, 'offer_id');
    }

    public function discount_status()
    {
        return $this->belongsTo(UserDiscountStatus::class, 'status');
    }
}
