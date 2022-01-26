<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmployeeRole
 * 
 * @property int $employee_id
 * @property int $role_id
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class EmployeeRole extends Model
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'employee_id' => 'int',
		'role_id' => 'int'
	];

    protected $fillable = [
        'employee_id',
        'role_id'
    ];
}
