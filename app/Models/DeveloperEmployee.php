<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeveloperEmployee
 * 
 * @property int $id
 * @property int $employee_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\Employee $employee
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class DeveloperEmployee extends Model
{
	protected $casts = [
		'employee_id' => 'int'
	];

	protected $fillable = [
		'employee_id'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}
}
