<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityLog
 * 
 * @property int $id
 * @property string $url
 * @property int $activity_type_id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\ActivityType $activity_type
 * @property \App\Models\User $user
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class ActivityLog extends Model
{
	protected $casts = [
		'activity_type_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'url',
		'activity_type_id',
        'description',
		'user_id'
	];

	public function activity_type()
	{
		return $this->belongsTo(\App\Models\ActivityType::class);
	}

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class);
	}
}
