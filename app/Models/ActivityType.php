<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityType
 * 
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $activity_logs
 * @property \Illuminate\Database\Eloquent\Collection $employees_activity_logs
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class ActivityType extends Model
{
	protected $fillable = [
		'name'
	];

	public function activity_logs()
	{
		return $this->hasMany(\App\Models\ActivityLog::class);
	}

	public function employees_activity_logs()
	{
		return $this->hasMany(\App\Models\EmployeesActivityLog::class);
	}
}
