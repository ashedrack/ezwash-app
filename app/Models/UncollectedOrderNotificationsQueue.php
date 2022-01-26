<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model;

/**
 * Class UncollectedOrderNotificationsQueue
 * 
 * @property int $id
 * @property int $order_id
 * @property int $throttle_count
 * @property Carbon $last_mail_sent_at
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Order $order
 * @property UncollectedOrderNotificationStatus $uncollected_order_notification_status
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class UncollectedOrderNotificationsQueue extends Model
{
	protected $table = 'uncollected_order_notifications_queue';

	protected $casts = [
		'order_id' => 'int',
		'throttle_count' => 'int',
		'status' => 'int'
	];

	protected $dates = [
		'last_mail_sent_at'
	];

	protected $fillable = [
		'order_id',
		'throttle_count',
        'process_id',
		'last_mail_sent_at',
		'status'
	];

    /**
     * @var array
     */
    protected $with = ['order'];

    const PENDING = 1;
    const PROCESSING = 2;
    const COLLECTED = 3;

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function uncollected_order_notification_status()
	{
		return $this->belongsTo(UncollectedOrderNotificationStatus::class, 'status');
	}
}
