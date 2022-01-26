<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PaymentMethod
 *
 * @property int $id
 * @property string $name
 *
 * @property \Illuminate\Database\Eloquent\Collection $orders
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class PaymentMethod extends Model
{
    const CARD_PAYMENT = 1;
    const CASH_PAYMENT = 2;
    const POS_PAYMENT = 3;

	public $timestamps = false;

	protected $fillable = [
		'name'
	];

	public function orders()
	{
		return $this->hasMany(\App\Models\Order::class, 'payment_method');
	}

	public function user_cards()
    {
        return $this->hasMany(UserCard::class);
    }

    public static function methodIsPosOrCash($paymentMethod)
    {
        return in_array($paymentMethod, [self::CASH_PAYMENT, self::POS_PAYMENT]);
    }
}
