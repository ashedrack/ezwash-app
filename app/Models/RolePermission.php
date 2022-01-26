<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RolePermission
 * 
 * @property int $role_id
 * @property int $permission_id
 * 
 * @property \App\Models\Permission $permission
 * @property \App\Models\Role $role
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class RolePermission extends Model
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'role_id' => 'int',
		'permission_id' => 'int'
	];

	public function permission()
	{
		return $this->belongsTo(\App\Models\Permission::class);
	}

	public function role()
	{
		return $this->belongsTo(\App\Models\Role::class);
	}
}
