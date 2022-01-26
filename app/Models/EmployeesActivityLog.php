<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmployeesActivityLog
 * 
 * @property int $id
 * @property string $url
 * @property int $activity_type_id
 * @property int $employee_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\Employee $employee
 * @property \App\Models\ActivityType $activity_type
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class EmployeesActivityLog extends Model
{
	protected $casts = [
		'activity_type_id' => 'int',
		'employee_id' => 'int'
	];

	protected $fillable = [
		'url',
		'activity_type_id',
		'employee_id',
        'description'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function activity_type()
	{
		return $this->belongsTo(\App\Models\ActivityType::class);
	}
}
