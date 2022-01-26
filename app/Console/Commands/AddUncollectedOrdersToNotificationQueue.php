<?php

namespace App\Console\Commands;

use App\Classes\Meta;
use App\Models\Order;
use App\Models\Setting;
use App\Models\UncollectedOrderNotificationQueue;
use App\Models\UncollectedOrderNotificationsQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddUncollectedOrdersToNotificationQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ready-for-pickup:add_to_queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add uncollected orders to the notification queues table.';

    protected $maxThrottleCount;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            $orders = Order::whereIn('status', [Meta::ORDER_STATUS_COMPLETED, Meta::ORDER_STATUS_PENDING])
                ->where('amount', '>', 0)
                ->where('collected', false)
                ->where('queued_as_uncollected', false)
                ->where('order_type', Meta::DROP_OFF_ORDER_TYPE)
                ->where('created_at', '>', '2020-09-20 00:00:00')
                ->orderBy('updated_at')
                ->take(100)
                ->get();
            if($orders->count() > 0) {
                $orderIDs = $orders->pluck('id')->toArray();

                $plucked = array_map(function ($id) {
                    return [
                        "order_id" => $id,
                        "created_at" => now()
                    ];
                }, $orderIDs); // Gets all id in this format [['order_id' => 4566, 'created_at' => '2020-09-11 09:20:10']]

                $result = UncollectedOrderNotificationsQueue::insertOrIgnore($plucked);

                Order::whereIn('id', $orderIDs)->update(['queued_as_uncollected' => true]);

                $message = 'Successfully queued ' . $result . ' uncollected orders for notification';

                logToFileAndDisplay($this, $message);
                return true;
            }
            return false;
        } catch(\Exception $e){
            logCriticalError('Error where processing \'ready-for-pickup:add_to_queue\'', $e);
            return false;
        }
    }


}
