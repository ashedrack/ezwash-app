<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class KwikPickupsAndDelivery
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property int $job_status
 * @property string $unique_order_id
 * @property string $credits
 * @property int $vehicle_id
 * @property Carbon $date
 * @property float $total_amount
 * @property float $actual_amount_paid
 * @property int $is_return_task
 * @property int $is_multiple_deliveries
 * @property string $sender_name
 * @property string $pickup_address
 * @property int $pickup_task_status
 * @property string $pickup_longitude
 * @property string $pickup_latitude
 * @property string $receiver_name
 * @property string $delivery_address
 * @property int $delivery_task_status
 * @property string $delivery_longitude
 * @property string $delivery_latitude
 * @property Carbon $started_datetime
 * @property Carbon $completed_datetime
 * @property bool $is_paid_for
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property KwikTaskStatus $deliveryTaskStatus
 * @property KwikTaskStatus $pickupTaskStatus
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class KwikPickupsAndDelivery extends Model
{
    protected $table = 'kwik_pickups_and_deliveries';

    protected $casts = [
        'user_id' => 'int',
        'order_id' => 'int',
        'order_request_id' => 'int',
        'job_status' => 'int',
        'vehicle_id' => 'int',
        'total_amount' => 'float',
        'actual_amount_paid' => 'float',
        'is_return_task' => 'int',
        'is_multiple_deliveries' => 'int',
        'pickup_task_status' => 'int',
        'delivery_task_status' => 'int',
        'is_paid_for' => 'bool'
    ];

    protected $dates = [
        'date',
        'started_datetime',
        'completed_datetime'
    ];

    protected $fillable = [
        'user_id',
        'order_id',
        'order_request_id',
        'job_status',
        'unique_order_id',
        'credits',
        'vehicle_id',
        'date',
        'total_amount',
        'actual_amount_paid',
        'is_return_task',
        'is_multiple_deliveries',
        'sender_name',
        'pickup_address',
        'pickup_task_status',
        'pickup_longitude',
        'pickup_latitude',
        'receiver_name',
        'delivery_address',
        'delivery_task_status',
        'delivery_longitude',
        'delivery_latitude',
        'started_datetime',
        'completed_datetime',
        'is_paid_for'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
	}

    public function order()
    {
        return $this->belongsTo(Order::class);
	}

	public function pickupTaskStatus()
    {
        return $this->belongsTo(KwikTaskStatus::class, 'pickup_task_status');
    }

	public function deliveryTaskStatus()
    {
        return $this->belongsTo(KwikTaskStatus::class, 'delivery_task_status');
    }
}
