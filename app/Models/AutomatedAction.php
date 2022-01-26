<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AutomatedAction
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class AutomatedAction extends Model
{
	protected $casts = [
		'status' => 'bool'
	];

	protected $fillable = [
		'name',
		'description',
		'status'
	];

	public static function isActive($actionName)
    {
        $action = self::where('name', $actionName)->first();
        if(!empty($action)){
            return $action->status;
        }
        return false;
    }
}
