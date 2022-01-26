<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 16 Oct 2019 11:23:00 +0000.
 */

namespace App\Models;

use App\Notifications\EmployeeResetPasswordNotification;
use App\Notifications\SetupPasswordNotification;
use App\Notifications\VerifyEmployeeEmail;
use App\Scopes\CompanyScope;
use App\Scopes\LocationScope;
use App\Traits\CoreModelMethods;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;



/**
 * Class Employee
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
 * @property int $company_id
 * @property int $is_active
 * @property string $prev_firebase_id
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 *
 * @property \App\Models\Employee $employee
 * @property \App\Models\Location $location
 * @property \App\Models\Company $company
 * @property \Illuminate\Database\Eloquent\Collection $companies
 * @property \Illuminate\Database\Eloquent\Collection $employees
 * @property \Illuminate\Database\Eloquent\Collection $employees_activity_logs
 * @property \Illuminate\Database\Eloquent\Collection $loyalty_offers
 * @property \Illuminate\Database\Eloquent\Collection $users
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Employee extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    use Notifiable;
    use EntrustUserTrait { restore as private restoreA; }
    use SoftDeletes { restore as private restoreB; }
    use CoreModelMethods;

    /**
     * fix collision on restore methods in SoftDelete trait and Entrust trait
     */
    public function restore()
    {
        $this->restoreA();
        $this->restoreB();
    }

    protected $casts = [
        'created_by' => 'int',
        'location_on_create' => 'int',
        'location_id' => 'int',
        'company_id' => 'int',
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
        'address',
        'avatar',
        'password',
        'created_by',
        'location_on_create',
        'location_id',
        'company_id',
        'is_active',
        'remember_token',
        'prev_firebase_id'
    ];

    protected $dates = [
        'email_verified_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
        static::addGlobalScope(new LocationScope);
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['roles'];

    public function creator()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'created_by');
    }

    public function first_name()
    {
        return explode(' ', $this->name, 2)[0];
    }

    public function last_name()
    {
        return explode(' ', $this->name, 2)[1];
    }


    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class, 'employee_roles');
    }

    public function getRolesAsString(){
        $roles = $this->roles()->pluck('display_name')->toArray();
        return implode(',', $roles);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function companies()
    {
        return $this->hasMany(\App\Models\Company::class, 'owner_id');
    }

    public function employees()
    {
        return $this->hasMany(\App\Models\Employee::class, 'created_by');
    }

    public function activities()
    {
        return $this->hasMany(\App\Models\EmployeesActivityLog::class);
    }

    public function loyalty_offers()
    {
        return $this->hasMany(\App\Models\LoyaltyOffer::class, 'created_by');
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'created_by');
    }

    public function isDev(){
        if(!empty(DeveloperEmployee::where('employee_id', $this->id)->first())) {
            return true;
        }
        return false;
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

    /**
     * @param string $role Role name
     */
    public function assignARole($role){
        $role = Role::where('name', $role)->first();
        if(!$this->hasRole($role->name)){
            $this->attachRole($role);
        }
    }

    public function _isOverallAdmin(){
        return $this->hasRole('overall_admin');
    }

    public function canViewLocation($location_id){
        $location = Location::find($location_id);
        if($this->_isOverallAdmin() || $location->company_id === $this->company->id ){
            return true;
        }
        return false;
    }

    /**
     * Get the employee's roles as a comma separated string of their display names
     * @return string
     */
    public function getRoles(){
        $roles = $this->roles()->get();
        $roleNames = "";
        $sn = 0;
        foreach ($roles as $role) {
            $roleNames .= $role->display_name . ($sn < $roles->count() - 1 ? "<br>" : "");
            $sn += 1;
        }
        return $roleNames;
    }

    public function _isActive(){
        if($this->is_active === 1 &&
            (is_null($this->location) || $this->location->_isActive()) &&
            (is_null($this->company) || $this->company->_isActive())
        ){
            return true;
        }
        return false;
    }
    public static function getAllowed($authUser = null){
        $authUser = $authUser ?? auth()->user();
        $builder = self::with('location', 'company');
        if($authUser && $authUser->location_id){
            $builder = $builder->where('location_id', $authUser->location_id);
        } elseif($authUser && $authUser->company_id){
            $builder = $builder->where('company_id', $authUser->company_id);
        }
        return $builder;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new EmployeeResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmployeeEmail);
    }

    public function sendPasswordSetupNotification($token = null )
    {
        $token = $token ?? $this->getResetToken();
        $this->notify(new SetupPasswordNotification($token));
    }

    public function broker(){
        return Password::broker('employees');
    }

    public function hasRole($name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && !$requireAll) {
                    return true;
                } elseif (!$hasRole && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll
            return $requireAll;
        } else {
            $role = $this->roles()->where('name', $name)->first();
            if(!empty($role)){
                return true;
            }
        }
        return false;
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

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    public function getResetToken()
    {
        return $this->broker()->getRepository()->create($this);
    }

}
