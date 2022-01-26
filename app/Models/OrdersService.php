<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrdersService
 *
 * @property int $id
 * @property int $order_id
 * @property int $service_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $quantity
 * @property float $price
 *
 * @property \App\Models\Order $order
 * @property \App\Models\Service $service
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class OrdersService extends Model
{
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'order_id' => 'int',
		'service_id' => 'int',
		'quantity' => 'int',
		'price' => 'float'
	];

	protected $fillable = [
		'order_id',
		'service_id',
		'quantity',
		'price'
	];

	public function order()
	{
		return $this->belongsTo(\App\Models\Order::class);
	}

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }
}
