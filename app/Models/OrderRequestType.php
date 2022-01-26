<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderRequestType
 * 
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class OrderRequestType extends Model
{
    const PICKUP = 1;
    const DELIVERY = 2;
	protected $table = 'order_request_types';

	protected $fillable = [
		'name'
	];

	public function order_requests()
    {
        return $this->hasMany(OrderRequest::class);
    }
}
