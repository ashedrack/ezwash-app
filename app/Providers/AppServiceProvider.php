<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrdersLocker;
use App\Models\User;
use App\Observers\EmployeeObserver;
use App\Observers\LocationObserver;
use App\Observers\OrderLockersObserver;
use App\Observers\OrderObserver;
use App\Observers\OrderRequestObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() == 'local') {
            $this->app->register(\Reliese\Coders\CodersServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Location::observe(LocationObserver::class);
        Employee::observe(EmployeeObserver::class);
        User::observe(UserObserver::class);
        Order::observe(OrderObserver::class);
        OrderRequest::observe(OrderRequestObserver::class);
        OrdersLocker::observe(OrderLockersObserver::class);
    }
}
