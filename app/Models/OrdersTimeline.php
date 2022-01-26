<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrdersTimeline
 * 
 * @property int $id
 * @property int $order_request_id
 * @property int $order_id
 * @property int $status_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class OrdersTimeline extends Model
{
	protected $table = 'orders_timelines';

	protected $casts = [
		'order_request_id' => 'int',
		'order_id' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'order_request_id',
		'order_id',
		'status_id'
	];

    public function order_requests()
    {
        $this->belongsTo(OrderRequest::class);
    }

    public function order()
    {
        $this->belongsTo(Order::class );
    }

    public function status()
    {
        $this->belongsTo(OrderRequestStatus::class, 'status_id' );
    }


}
