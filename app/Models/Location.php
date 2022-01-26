<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use App\Traits\CoreModelMethods;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Location
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $store_image
 * @property int $number_of_lockers
 * @property int $company_id
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property float $longitude
 * @property float $latitude
 * @property string $prev_firebase_id
 * @property bool $is_visible
 *
 * @property \App\Models\Company $company
 * @property \Illuminate\Database\Eloquent\Collection $employees
 * @property \Illuminate\Database\Eloquent\Collection $employees_revs
 * @property \Illuminate\Database\Eloquent\Collection $lockers
 * @property \Illuminate\Database\Eloquent\Collection $orders
 * @property \Illuminate\Database\Eloquent\Collection $users
 * @property \Illuminate\Database\Eloquent\Collection $users_revs
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Location extends Model
{
	use \Illuminate\Database\Eloquent\SoftDeletes;
	use CoreModelMethods;

	protected $casts = [
		'number_of_lockers' => 'int',
		'company_id' => 'int',
		'is_active' => 'int',
		'longitude' => 'float',
		'latitude' => 'float',
		'is_visible' => 'bool'
	];

	protected $fillable = [
		'name',
		'address',
		'phone',
		'store_image',
		'number_of_lockers',
		'company_id',
		'is_active',
		'longitude',
		'latitude',
        'prev_firebase_id',
        'is_visible'
	];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

	public function company()
	{
		return $this->belongsTo(\App\Models\Company::class);
	}

	public function employees()
	{
		return $this->hasMany(\App\Models\Employee::class);
	}


	public function lockers()
	{
		return $this->hasMany(\App\Models\Locker::class);
	}

	public function orders()
	{
		return $this->hasMany(\App\Models\Order::class);
	}

	public function users()
	{
		return $this->hasMany(\App\Models\User::class);
	}

	public static function getAllowed($guard = null){
	    $employee = auth($guard)->user();
        if(!$employee->company_id){
            return Location::all();
        }else{
            if($employee->location_id){
                return Location::where('id', $employee->location_id)->get();
            }
            return Location::where('company_id', $employee->company_id)->get();
        }
    }

    /**
     * @param null $authUser
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function allowedToAccess($authUser = null){
        $employee = $authUser ?? auth()->user();
        if(!$employee->company_id){
            return self::query();
        } else {
            return Location::where('company_id', $employee->company->id);
        }
    }

    public function total_sales(){
	    if(empty($this->orders)){
	        return 0;
        }
        $completedOrders = Order::where('location_id' , $this->id)
            ->where('status' , ORDER_STATUS_COMPLETED)
            ->sum('amount');
        return $completedOrders;
    }

    public function pending_sales(){
        if(empty($this->orders)){
            return 0;
        }
        $pendingOrders = Order::where('location_id' , $this->id)
            ->where('status' , ORDER_STATUS_PENDING)
            ->sum('amount');
        return $pendingOrders;
    }

    public function avg_monthly_sales(){
        return 0;
    }

    public function today_sales()
    {
        if(empty($this->orders)){
            return 0;
        }
        $today_sales = Order::where('location_id', $this->id)
            ->where('status', ORDER_STATUS_COMPLETED)
            ->whereDate('updated_at', now())
            ->sum('amount');

        return $today_sales;
    }
}
