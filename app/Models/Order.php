<?php

namespace App\Models;

use App\Classes\Meta;
use App\Scopes\CompanyScope;
use App\Scopes\LocationScope;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\ProcessUserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

/**
 * Class Order
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_type
 * @property int $status
 * @property float $amount
 * @property float $pickup_cost
 * @property float $delivery_cost
 * @property int $payment_method
 * @property int $created_by
 * @property int $location_id
 * @property int $company_id
 * @property bool $collected
 * @property string $note
 * @property int $bags
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $completed_at
 * @property string $prev_firebase_id
 * @property string $firebase_meta
 * @property string $locker_numbers
 *
 * @property \App\Models\Employee $order_creator
 * @property \App\Models\Company $company
 * @property \App\Models\Location $location
 * @property \App\Models\OrdersStatus $orders_status
 * @property \App\Models\User $user
 * @property \App\Models\PaymentMethod $paymentMethod
 * @property \App\Models\OrderType $orderType
 * @property \Illuminate\Database\Eloquent\Collection $orders_discounts
 * @property \Illuminate\Database\Eloquent\Collection|Transaction $transaction
 * @property \Illuminate\Database\Eloquent\Collection|Locker[] $lockers
 * @property void $emptyRelatedLockers
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Order extends Model
{
	use \Illuminate\Database\Eloquent\SoftDeletes;

	protected $casts = [
		'user_id' => 'int',
		'order_type' => 'int',
		'status' => 'int',
		'amount' => 'float',
        'pickup_cost' => 'float',
        'delivery_cost' => 'float',
        'amount_before_discount' => 'float',
		'payment_method' => 'int',
		'created_by' => 'int',
		'location_id' => 'int',
		'company_id' => 'int',
		'collected' => 'bool',
		'bags' => 'int'
	];

	protected $fillable = [
		'user_id',
		'order_type',
		'status',
        'amount',
        'pickup_cost',
        'delivery_cost',
        'amount_before_discount',
		'payment_method',
		'created_by',
		'location_id',
		'company_id',
        'collected',
        'completed_at',
		'note',
		'bags',
        'prev_firebase_id',
        'firebase_meta',
        'locker_numbers'
	];

    protected $with = ['location', 'user', 'paymentMethod', 'order_status', 'orderType'];
    const COALESCE_DATE_STRING = "COALESCE(orders.completed_at,orders.created_at)";

    /**
     * Scope a query to only include popular users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  Authenticatable|Employee $authUser
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAllowed($query, $authUser)
    {
        if($authUser->location_id) {
            return $query->where('location_id', $authUser->location_id);
        } elseif($authUser->company_id){
            return $query->where('company_id', $authUser->company_id);
        } else {
            return $query;
        }
    }

    public function company()
	{
		return $this->belongsTo(\App\Models\Company::class);
	}

	public function location()
	{
		return $this->belongsTo(\App\Models\Location::class)->withTrashed();
	}

	public function order_status()
	{
		return $this->belongsTo(\App\Models\OrdersStatus::class, 'status');
	}

	public function orderType()
	{
		return $this->belongsTo(\App\Models\OrderType::class, 'order_type');
	}

	public function paymentMethod()
	{
	    return $this->belongsTo(PaymentMethod::class, 'payment_method');
	}

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class)->withTrashed();
	}

	public function discount()
	{
		return $this->hasOne(OrdersDiscount::class);
	}

	public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

	public function discountApplied()
    {
        $discountDetails = $this->userDiscount;
        if(isset($discountDetails) && !empty($discountDetails)){
            return $discountDetails->discount_earned;
        }
        return null;
    }

	public function userDiscount()
    {
        /**
         * select * from `users_discounts` inner join `orders_discounts` on `orders_discounts`.`secondLocalKey(users_discount_id)` = `users_discounts`.`secondKey(id)`
         * where `orders_discounts`.`deleted_at` is null and `orders_discounts`.`order_id` is ($this->id)
         */
        return $this->hasOneThrough(UsersDiscount::class, OrdersDiscount::class, 'order_id', 'id', 'id', 'users_discount_id');
    }

	public function lockers()
	{
		return $this->belongsToMany(\App\Models\Locker::class, 'orders_lockers')
					->withPivot('id', 'deleted_at')
					->withTimestamps();
	}

	public function order_services()
	{
		return $this->hasMany(OrdersService::class);
	}

	public function services()
    {
        return $this->belongsToMany(Service::class, 'orders_services' )
            ->withPivot('id')
            ->withTimestamps()
            ->withTrashed();
    }

	public function requiresPaymentAction(){
        if($this->amount > 0 &&
            $this->status == Meta::ORDER_STATUS_PENDING &&
            !PaymentMethod::methodIsPosOrCash($this->payment_method)
        ){
            return true;
        }
        return false;
    }

    public function order_requests()
    {
        return $this->hasMany(OrderRequest::class);
    }
    public function order_creator()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'created_by');
    }

    public function hasPickupRequest(){
        return $this->order_requests()->where('order_request_type_id', OrderRequestType::PICKUP)->whereNotIn('order_request_status_id', [
            OrderRequestStatus::PICKUP_CANCELED,
            OrderRequestStatus::REQUEST_FAILED
        ])->exists();
    }

    public function hasDeliveryRequest($includeUnpaid = false){
        return $this->order_requests()->where('order_request_type_id', OrderRequestType::DELIVERY)
            ->where(function ($query) use ($includeUnpaid){
                $query = $query->whereNotIn('order_request_status_id', [
                        OrderRequestStatus::DELIVERY_CANCELLED,
                        OrderRequestStatus::REQUEST_FAILED
                    ]);
                if($includeUnpaid){
                    $query = $query->orWhereNull('order_request_status_id');
                }
            })->exists();
    }

    public function unpaidDeliveryRequest(){
        return $this->order_requests()
            ->where('order_request_type_id', OrderRequestType::DELIVERY)
            ->whereNull('order_request_status_id');
    }

    public function pickupRequest(){
        return $this->order_requests()->where('order_request_type_id', OrderRequestType::PICKUP)
            ->whereNotIn('order_request_status_id', [
                OrderRequestStatus::PICKUP_CANCELED,
                OrderRequestStatus::REQUEST_FAILED
            ])->latest()->first();
    }

    /**
     * @return OrderRequest|Model|null
     */
    public function deliveryRequest(){
        return $this->order_requests()->where('order_request_type_id', OrderRequestType::DELIVERY)
            ->where(function ($query){
                $query->whereNull('order_request_status_id')
                    ->orWhereNotIn('order_request_status_id', [
                        OrderRequestStatus::DELIVERY_CANCELLED,
                        OrderRequestStatus::REQUEST_FAILED
                    ]);
            })->latest()->first();
    }

    public function getAmountToPay()
    {
        return ($this->amount
            + ($this->hasPickupRequest() ? $this->pickupRequest()->amount : 0)
            + (($this->hasDeliveryRequest(true)) ? $this->deliveryRequest()->amount : 0)
        );
    }

    public function markAsCollected()
    {
        $lockerArr = $this->lockers->pluck('locker_number','id')->toArray();
        $this->lockers()->detach();
        Locker::whereIn('id', array_keys($lockerArr ?? []))
            ->update(['occupied' => 0]);

        $this->update([
            'collected' => 1
        ]);
        $customer = $this->user;
        Queue::push(new ProcessUserNotification(
            $customer,
            '\App\Notifications\CollectedOrderNotification',
            [$this]
        ));

    }

    public function emptyRelatedLockers()
    {
        $lockerArr = $this->lockers->pluck('locker_number','id')->toArray();
        $this->lockers()->detach();
        Locker::whereIn('id', array_keys($lockerArr ?? []))
            ->update(['occupied' => 0]);

        if(!empty($lockerArr)) {
            $this->update([
                'locker_numbers' => json_encode(array_values($lockerArr)),
            ]);
        }

    }
}
