<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Zizaco\Entrust\EntrustRole;

/**
 * Class Role
 * 
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $permissions
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Role extends EntrustRole
{
	protected $fillable = [
		'name',
        'hierarchy',
		'display_name',
		'description'
	];

	public function permissions()
	{
		return $this->belongsToMany(\App\Models\Permission::class, 'role_permissions');
	}

	public static function getAllowed(){
	    $maxHierarchy = auth()->user()->roles->max('hierarchy');
	    return Role::where('hierarchy', '<=', $maxHierarchy);
    }
}
