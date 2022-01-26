<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderType
 *
 * @property int $id
 * @property string $name
 *
 * @property \Illuminate\Database\Eloquent\Collection $orders
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class OrderType extends Model
{
	public $timestamps = false;

    const SELF_SERVICE = 1;
    const DROP_OFF = 2;

	protected $fillable = [
		'name'
	];

	public function orders()
	{
		return $this->hasMany(\App\Models\Order::class, 'order_type');
	}
}
