<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderRequestStatus
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class  OrderRequestStatus extends Model
{
    const PICKUP_REQUESTED = 1;
    const PICKUP_CANCELED = 2;
    const PICKUP_STARTED = 3;
    const PICKED_UP = 4;
    const DROPPED_OFF = 5;
    const WASH_IN_PROGRESS = 6;
    const READY_FOR_COLLECTION = 7;
    const DELIVERY_REQUESTED = 8;
    const DELIVERY_CANCELLED = 9;
    const DELIVERY_STARTED = 10;
    const PICKED_UP_FOR_DELIVERY = 11;
    const ORDER_DELIVERED = 12;
    const REQUEST_FAILED = 13;
    const DELIVERY_MANUALLY_SORTED = 14;

	protected $table = 'order_request_statuses';

	protected $fillable = [
		'name',
		'display_name',
		'description'
	];

	public function order_requests()
    {
        $this->hasMany(OrderRequest::class);
    }

    public function orders()
    {
        $this->hasManyThrough(Order::class, OrderRequest::class);
    }

    public static function trackableStatuses()
    {
        return [
            self::PICKUP_STARTED,
            self::PICKED_UP,
            self::DELIVERY_STARTED,
            self::PICKED_UP_FOR_DELIVERY
        ];
    }
}
