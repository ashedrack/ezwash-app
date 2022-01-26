<?php

namespace App\Console\Commands;

use App\Classes\Meta;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Notifications\ReadyForPickupNotification;
use Illuminate\Console\Command;

class ReadyForPickup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:ready-for-pickup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify user that the order is ready for pickup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = Order::leftJoin('order_requests', function($join)
        {
            $join->on('orders.id', 'order_requests.order_id');
        })->where('orders.status', Meta::ORDER_STATUS_COMPLETED)->where('orders.collected', false)
            ->where('order_requests.request_type', OrderRequest::PICKUP_TYPE)->get();
        foreach($orders as $order)
        {
            $order->user->notify(new ReadyForPickupNotification($order->user));
        }
        return;

    }
}
