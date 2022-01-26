<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;

class OrderObserver
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|Employee|User|null
     */
    protected $authUser;

    public function __construct()
    {
        $this->authUser = (auth('web')->check())? auth()->user() : auth('admins')->user() ;
    }

    /**
     * Handle the order "created" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'created_order',
                    'url' => route('order.view', ['order' => $order->id]),
                    'description' => "Created an order"
                ]
            ]);
        }
        if($order->status == ORDER_STATUS_COMPLETED){
            $order->update(['completed_at' => now()]);
        }
    }

    /**
     * Handle the order "updated" event.
     *
     * @param  \App\Models\Order $order
     * @return void
     */
    public function updated(Order $order)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'updated_order',
                    'url' => route('order.view', ['order' => $order->id]),
                    'description' => "Updated an order"
                ]
            ]);
        }
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param  \App\Models\Order $order
     * @return void
     */
    public function deleted(Order $order)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'deleted_order',
                    'description' => "Deleted an order"
                ]
            ]);
        }
    }

    /**
     * Handle the order "restored" event.
     *
     * @param  \App\Models\Order $order
     * @return void
     */
    public function restored(Order $order)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'restored_an_order',
                    'url' => route('order.view', ['order' => $order->id]),
                    'description' => "Restored an order that was temporarily deleted"
                ]
            ]);
        }
    }

    /**
     * Handle the order "force deleted" event.
     *
     * @param  \App\Models\Order $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        if(!is_null($this->authUser)){
            $this->authUser->recordActivity([
                [
                    'name' => 'permanently_deleted_order',
                    'description' => "Deleted an order permanently"
                ]
            ]);
        }
    }
}
