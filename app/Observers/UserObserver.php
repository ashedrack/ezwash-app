<?php

namespace App\Observers;

use App\Jobs\ProcessUserNotification;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Queue;

class UserObserver
{
    protected $authUser;

    public function __construct()
    {
        $this->authUser = auth()->user();
    }

    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'customer_added',
                    'url' => route('customer.view', ['customer' => $user->id]),
                    'description' => "Added a customer:  [Name:$user->name | Email: $user->email]"
                ]
            ]);
        }
        $userToken = $user->getResetToken();
        Queue::push(new ProcessUserNotification(
            $user,
            '\App\Notifications\CustomerPasswordSetupNotification',
            [$userToken]
        ));
    }

    /** Handle the user "accountSetupCompleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function accountSetupCompleted(User $user)
    {
        $user->recordActivity([
            [
                'name' => 'account_setup',
                'url' => route('customer.view', ['customer' => $user->id]),
                'description' => "Completed the account setup process"
            ]
        ]);
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'customer_updated',
                    'url' => route('customer.view', ['customer' => $user->id]),
                    'description' => "Updated a customer: [Name:$user->name | Email: $user->email]"
                ]
            ]);
        }
    }

    /**
     * Handle the user "deactivated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deactivated(User $user)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'deactivated_customer',
                    'url' => route('customer.view', ['customer' => $user->id]),
                    'description' => "Deactivated a customer (Name:$user->name | Email: $user->email)"
                ]
            ]);
        }
    }

    /**
     * Handle the user "activated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function activated(User $user)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'activated_customer',
                    'url' => route('customer.view', ['customer' => $user->id]),
                    'description' => "Activated a customer (Name:$user->name | Email: $user->email)"
                ]
            ]);
        }
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'deleted_customer',
                    'description' => "Deleted a customer: [Name:$user->name | Email: $user->email]"
                ]
            ]);
        }
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'restored_a_customer',
                    'url' => route('customer.view', ['customer' => $user->id]),
                    'description' => "Restored a customer that was temporarily deleted: [Name:$user->name | Email: $user->email]"
                ]
            ]);
        }
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'permanently_deleted_customer',
                    'description' => "Deleted a customer permanently: [Name:$user->name | Email: $user->email]"
                ]
            ]);
        }
    }
}
