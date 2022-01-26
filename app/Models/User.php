<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use App\Classes\Meta;
use App\Notifications\CustomerPasswordResetNotification;
use App\Notifications\CustomerPasswordSetupNotification;
use App\Notifications\CustomerResendPasswordSetupNotification;
use App\Traits\CoreModelMethods;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 *
 * @property int $id
 * @property string $email
 * @property string $phone
 * @property string $name
 * @property string $gender
 * @property string $avatar
 * @property string $password
 * @property int $created_by
 * @property int $location_on_create
 * @property int $location_id
 * @property string $notification_player_id
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 *
 * @property \App\Models\Employee $employee
 * @property \App\Models\Location $location
 * @property \Illuminate\Database\Eloquent\Collection $activity_logs
 * @property \Illuminate\Database\Eloquent\Collection $orders
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    use Notifiable;
    use SoftDeletes;
    use CoreModelMethods;

	protected $casts = [
		'created_by' => 'int',
		'location_on_create' => 'int',
		'location_id' => 'int',
        'is_active' => 'int'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'email',
		'phone',
		'name',
		'gender',
		'avatar',
		'password',
		'created_by',
		'location_on_create',
		'location_id',
		'notification_player_id',
		'remember_token',
        'is_active'
	];

    /**
     * Array of our custom model events declared under model property $observables
     * @var array
     */

    protected $observables = [
        'accountSetupCompleted',
        'deactivated',
        'activated'
    ];


    /**
     * Setup account & fire custom model event
     *
     */
    public function accountSetup()
    {
        $this->fireModelEvent('accountSetupCompleted', false);
    }

	public function created_by()
	{
		return $this->belongsTo(\App\Models\Employee::class, 'created_by');
	}

	public function location()
	{
		return $this->belongsTo(\App\Models\Location::class);
	}

	public function activities()
	{
		return $this->hasMany(\App\Models\ActivityLog::class);
	}

    /**
     * @param array[] $activities An array of activities
     */
    public function recordActivity($activities){
        if(!empty($activities)) {
            foreach ($activities as $activity) {
                $type = ActivityType::firstOrCreate(['name' => $activity['name']]);
                $this->activities()->create([
                    'activity_type_id' => $type->id,
                    'description' => $activity['description'],
                    'url' => (isset($activity['url']))? $activity['url'] : null
                ]);
            }
        }
    }

	public function orders()
	{
		return $this->hasMany(\App\Models\Order::class);
	}

	public function completedOrders(){
        return $this->hasMany(\App\Models\Order::class)->where('status', ORDER_STATUS_COMPLETED);
    }

    public function pendingOrders(){
        return $this->hasMany(\App\Models\Order::class)->where('status', ORDER_STATUS_PENDING);
    }

	public function transactionsByPaymentMethod(){
	    $transactions = DB::table('payment_methods as pm')
            ->leftJoin('orders', function ($join){
                $join->on('orders.payment_method', '=', 'pm.id')
                    ->where('orders.user_id', '=', $this->id)
                    ->where('orders.status', ORDER_STATUS_COMPLETED);
            })
            ->select('pm.id as id', 'pm.name as payment_method', DB::raw('IF(SUM(amount) is null, 0, SUM(amount)) as amount'))
            ->groupBy('pm.id')
            ->get();
	    $methodIds = $transactions->pluck('id')->toArray();
	    $payload = array_values($transactions->toArray());
	    return array_combine($methodIds, $payload );
    }

    public function sendPasswordSetupNotification($token = null)
    {
        $userToken = $token ?? $this->getResetToken();
        $this->notify(new CustomerPasswordSetupNotification($userToken));
    }

    public function resendPasswordSetupNotification()
    {
        $userToken = $this->getResetToken();
        $this->notify(new CustomerResendPasswordSetupNotification($userToken));
    }

    public function sendUserPasswordResetNotification()
    {
        $userToken = $this->getResetToken();
        $this->notify(new CustomerPasswordResetNotification($userToken));
    }

    public function broker(){
        return 'users';
    }

    public function discounts()
    {
        return $this->hasMany(UsersDiscount::class );
    }

    public function unusedDiscount()
    {
        return $this->hasOne(UsersDiscount::class)->where('status', Meta::UNUSED_DISCOUNT);
    }

    public function usedDiscounts()
    {
        return $this->hasMany(UsersDiscount::class )->where('status', Meta::USED_DISCOUNT);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function userAddresses(){

        return $this->hasMany(UserAddress::class);

    }
    public function userHomeAddress(){

        return $this->hasOne(UserAddress::class)->where('is_home_address', Meta::IS_HOME_ADDRESS);

    }
    public function user_cards()
    {
        return $this->hasMany(UserCard::class);
    }

    public function firstName(){
        $names = explode(' ', $this->name);
        return $names[0];
    }

    public function deleteWithRelated()
    {
        $this->orders()->where('status', ORDER_STATUS_PENDING)->delete();
        $this->delete();
    }

    public function hasUnmatchedPickup($locationID, $requestDate = null)
    {
        $requestDate = $requestDate ?? now()->toDateString();
        return OrderRequest::where('user_id', $this->id)
            ->where('location_id', $locationID)
            ->where('order_request_type_id', OrderRequestType::PICKUP)
            ->whereIn('order_request_status_id', [OrderRequestStatus::PICKED_UP, OrderRequestStatus::DROPPED_OFF])
            ->whereNull('order_id')
            ->whereDate('created_at', $requestDate)->exists();
    }

    public function getResetToken()
    {
        return Password::broker('users')->getRepository()->create($this);
    }
}
