<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TempOrderRequest
 *
 * @property int $id
 * @property int $user_id
 * @property int $location_id
 * @property string $pickup_name
 * @property string $pickup_address
 * @property float $pickup_latitude
 * @property float $pickup_longitude
 * @property Carbon $pickup_time
 * @property string $pickup_phone
 * @property integer $pickup_task_status
 * @property string $delivery_name
 * @property string $delivery_address
 * @property float $delivery_latitude
 * @property float $delivery_longitude
 * @property Carbon $delivery_time
 * @property string $delivery_phone
 * @property integer $delivery_task_status
 * @property string $request_type
 * @property string $note
 * @property float $amount
 * @property bool $scheduled
 * @property bool $accepted
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property float $actual_estimate
 * @property int $order_id
 * @property string $unique_order_id
 *
 * @property OrderRequest $order_request
 * @property User $user
 * @property Location $location
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class TempOrderRequest extends Model
{
    const PICKUP_TYPE = 'pickup';
    const DELIVERY_TYPE = 'delivery';

	protected $table = 'temp_order_requests';

	protected $casts = [
        'user_id' => 'int',
        'order_id' => 'int',
		'location_id' => 'int',
		'pickup_latitude' => 'float',
		'pickup_longitude' => 'float',
		'delivery_latitude' => 'float',
		'delivery_longitude' => 'float',
		'amount' => 'float',
        'actual_estimate' => 'float',
		'scheduled' => 'bool',
		'accepted' => 'bool'
	];

	protected $dates = [
		'pickup_time',
		'delivery_time'
	];

	protected $fillable = [
		'user_id',
        'order_id',
		'location_id',
		'pickup_name',
		'pickup_address',
		'pickup_latitude',
		'pickup_longitude',
		'pickup_time',
		'pickup_phone',
		'delivery_name',
		'delivery_address',
		'delivery_latitude',
		'delivery_longitude',
		'delivery_time',
		'delivery_phone',
		'request_type',
		'note',
        'actual_estimate',
		'amount',
		'scheduled',
		'accepted',
        'unique_order_id'
	];

	public function user()
    {
        return $this->belongsTo(User::class);
    }

	public function order_request()
    {
        return $this->belongsTo(OrderRequest::class, 'unique_order_id', 'kwik_order_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
