<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Zizaco\Entrust\EntrustPermission;

/**
 * Class Permission
 * 
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $group_id
 * 
 * @property \App\Models\PermissionGroup $permission_group
 * @property \Illuminate\Database\Eloquent\Collection $roles
 *
 * @package App\Models
 * @mixin \Eloquent
 */

class Permission extends EntrustPermission
{
	protected $casts = [
		'group_id' => 'int'
	];

	protected $fillable = [
		'name',
		'display_name',
		'description',
		'group_id'
	];

	public function permission_group()
	{
		return $this->belongsTo(\App\Models\PermissionGroup::class, 'group_id');
	}

	public function roles()
	{
		return $this->belongsToMany(\App\Models\Role::class, 'role_permissions');
	}
}
