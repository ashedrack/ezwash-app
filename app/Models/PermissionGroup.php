<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 12 Oct 2019 14:44:20 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PermissionGroup
 * 
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $permissions
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class PermissionGroup extends Model
{
	protected $fillable = [
		'name',
		'display_name'
	];

	public function permissions()
	{
		return $this->hasMany(\App\Models\Permission::class, 'group_id');
	}
}
