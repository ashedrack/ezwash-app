<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

/**
 * Class Service
 *
 * @property int $id
 * @property string $name
 * @property int $price
 * @property int $company_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $prev_firebase_id
 * @property string $deleted_at
 *
 * @property \Illuminate\Database\Eloquent\Collection $orders
 * @property Company|null $company
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Service extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

	protected $casts = [
        'price' => 'int',
        'company_id' => 'int',
	];

	protected $fillable = [
		'name',
		'price',
        'company_id',
        'total',
        'prev_firebase_id'
	];

    /**
     * Scope a query to only include popular users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAllowedToAccess($query)
    {
        $authUser = auth()->user();
        if($authUser && $authUser->company_id){
            return $query->where('company_id', $authUser->company_id);
        }
        return $query;
    }

	public function orders()
	{
		return $this->belongsToMany(\App\Models\Order::class, 'orders_services')
					->withPivot('id')
					->withTimestamps();
	}

	public function usage(){
	    return OrdersService::whereHas('order', function ($q){
	        $q->where('status', ORDER_STATUS_COMPLETED);
        })->where('service_id', $this->id)->sum('quantity');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
