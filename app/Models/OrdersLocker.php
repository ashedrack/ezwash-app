<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrdersLocker
 * 
 * @property int $id
 * @property int $order_id
 * @property int $locker_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * 
 * @property \App\Models\Locker $locker
 * @property \App\Models\Order $order
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class OrdersLocker extends Model
{
	use \Illuminate\Database\Eloquent\SoftDeletes;

	protected $casts = [
		'order_id' => 'int',
		'locker_id' => 'int'
	];

	protected $fillable = [
		'order_id',
		'locker_id'
	];

	public function locker()
	{
		return $this->belongsTo(\App\Models\Locker::class);
	}

	public function order()
	{
		return $this->belongsTo(\App\Models\Order::class);
	}
}
