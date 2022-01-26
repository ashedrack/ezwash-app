<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 05 Aug 2019 04:29:56 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PermissionRole
 * 
 * @property int $permission_id
 * @property int $role_id
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class PermissionRole extends Model
{
	protected $table = 'permission_role';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'permission_id' => 'int',
		'role_id' => 'int'
	];
}
