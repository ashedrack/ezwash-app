<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Reliese\Database\Eloquent\Model;

/**
 * Class UncollectedOrderNotificationStatus
 * 
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Collection|UncollectedOrderNotificationsQueue[] $uncollected_order_notifications_queues
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class UncollectedOrderNotificationStatus extends Model
{
	protected $table = 'uncollected_order_notification_statuses';

	protected $fillable = [
		'name'
	];

	public function uncollected_order_notifications_queues()
	{
		return $this->hasMany(UncollectedOrderNotificationsQueue::class, 'status');
	}
}
