<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Locker
 * 
 * @property int $id
 * @property int $location_id
 * @property int $locker_number
 * @property int $occupied
 * 
 * @property \Illuminate\Database\Eloquent\Collection $orders
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Locker extends Model
{
    use SoftDeletes;
	public $timestamps = false;

	protected $casts = [
		'location_id' => 'int',
		'locker_number' => 'int',
		'occupied' => 'int'
	];

	protected $fillable = [
		'location_id',
		'locker_number',
		'occupied'
	];

	public function orders()
	{
		return $this->belongsToMany(\App\Models\Order::class, 'orders_lockers')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}
}
