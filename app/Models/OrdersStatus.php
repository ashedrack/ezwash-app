<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrdersStatus
 *
 * @property int $id
 * @property string $name
 * @property string $description
 *
 * @property \Illuminate\Database\Eloquent\Collection $orders
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class OrdersStatus extends Model
{
	public $timestamps = false;

	const PENDING = 1;
	const COMPLETED = 2;

	protected $fillable = [
		'name',
		'description'
	];

	public function orders()
	{
		return $this->hasMany(\App\Models\Order::class, 'status');
	}
}
