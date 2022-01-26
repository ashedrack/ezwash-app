<?php

namespace App\Observers;

use App\Models\OrdersLocker;

class OrderLockersObserver
{
    /**
     * Handle the order "created" event.
     *
     * @param \App\Models\OrdersLocker $orderLocker
     * @return void
     */
    public function created(OrdersLocker $orderLocker)
    {
        $order = $orderLocker->order;
        $lockerNums = $order->lockers->pluck('locker_number')->toArray();
        if(!empty($lockerNums)) {
            $order->update(['locker_numbers' => json_encode($lockerNums)]);
        }
    }

    /**
     * Handle the order "created" event.
     *
     * @param \App\Models\OrdersLocker $orderLocker
     * @return void
     */
    public function deleted(OrdersLocker $orderLocker)
    {
        $order = $orderLocker->order;
        $lockerNums = $order->lockers->pluck('locker_number')->toArray();
        if(!empty($lockerNums)) {
            $order->update(['locker_numbers' => json_encode($lockerNums)]);
        }
    }
}
