<?php

namespace App\Console\Commands;

use App\Models\AutomatedAction;
use App\Models\Order;
use App\Models\UncollectedOrderNotificationsQueue;
use App\Notifications\ReadyForPickupNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HandleTheUncollectedOrderNotificationsQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ready-for-pickup:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify user that the order is ready for pickup';

    const STATUS_PENDING = UncollectedOrderNotificationsQueue::PENDING;
    const STATUS_PROCESSING = UncollectedOrderNotificationsQueue::PROCESSING;
    const STATUS_COLLECTED = UncollectedOrderNotificationsQueue::COLLECTED;

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
        $notification_hours = 24;
        $currentTime = now()->toDateTimeString();
        $throttleLimit = config('app.MAX_THROTTLE_COUNT_FOR_COLLECTION_NOTICE');
        $actionName = 'process_uncollected_order_notifications';
        if(!AutomatedAction::isActive($actionName)){
            $this->info("Action '{$actionName}' is deactivated");
            return false;
        }

        $queuedOrders = UncollectedOrderNotificationsQueue::where(function($q) use ($currentTime) {
                $q->whereRaw("DATEDIFF(last_mail_sent_at, '{$currentTime}') >= 1")
                    ->orWhereNull('last_mail_sent_at');
            })
            ->where('status', self::STATUS_PENDING)
            ->where('throttle_count', '<', $throttleLimit)
            ->where('process_id', null)
            ->orderBy('last_mail_sent_at', 'ASC')
            ->orderBy('throttle_count', 'ASC')
            ->limit(200)
            ->get();

        $ids = $queuedOrders->pluck('id')->toArray();
        $processId = uniqid('UON-');
        UncollectedOrderNotificationsQueue::whereIn('id', $ids)
            ->where('status', self::STATUS_PENDING)
            ->update([
                'status' => self::STATUS_PROCESSING,
                'process_id' => $processId
            ]);

        $entriesToProcess = UncollectedOrderNotificationsQueue::whereIn('id', $ids)
            ->where('status', self::STATUS_PROCESSING)
            ->where('process_id', $processId)->get();

        $report = (object)[
            'count_to_process' => count($entriesToProcess),
            'count_processed' => 0,
            'error_payload' => [],
            'success_payload' => [],
            'process_id' => $processId,
            'ended_early' => false,
        ];

        foreach ($entriesToProcess as $entry)
        {
            try {
                if (!AutomatedAction::isActive($actionName)) {
                    $this->info("Action '{$actionName}' is deactivated");
                    // Set unprocessed orders to pending and end the loop
                    UncollectedOrderNotificationsQueue::whereIn('id', $ids)
                        ->where('process_id', $processId)
                        ->where('status', self::STATUS_PROCESSING)
                        ->update([
                            'status' => self::STATUS_PENDING,
                            'process_id' => null
                        ]);
                    $report->ended_early = true;
                    return false;
                }
                $order = Order::where('id', $entry->order_id)->first();
                if ($order->collected) {
                    $metadata = ['id' => $entry->id, 'message' => 'Already collected', 'process_id' => $processId];
                    $entry->update([
                        'status' => self::STATUS_COLLECTED,
                        'metadata' => json_encode($metadata),
                    ]);
                    $report->success_payload[] = $metadata;
                    $report->count_processed += 1;
                    continue;
                } else {
                    $order->user->notify(new ReadyForPickupNotification($order));
                    $metadata = ['id' => $entry->id, 'message' => 'User notified', 'process_id' => $processId];
                    $report->success_payload[] = $metadata;
                    $report->count_processed += 1;
                    $entry->update([
                        'status' => self::STATUS_PENDING,
                        'metadata' => json_encode($metadata),
                        'throttle_count' => ($entry->throttle_count + 1),
                        'last_mail_sent_at' => now()->toDateTimeString(),
                    ]);
                }
            } catch (\Exception $e){
                $metadata = ['id' => $entry->id, 'message' => 'An Error Occurred:: '. $e->getMessage(), 'process_id' => $processId];
                $confirmEntry = UncollectedOrderNotificationsQueue::where('id', $entry->id)->first();

                // If failure occurred before entry was marked as processed
                if($confirmEntry->status == self::STATUS_PROCESSING) {
                    $entry->update([
                        'status' => self::STATUS_PENDING,
                        'metadata' => json_encode($metadata),
                        'throttle_count' => ($entry->throttle_count + 1),
                    ]);
                }
                $report->error_payload[] = $metadata;
                $report->count_processed += 1;
                continue;
            }
        }

        UncollectedOrderNotificationsQueue::where('process_id', $processId)
            ->update(['process_id' => null]);

        logToFileAndDisplay($this, json_encode($report));
        return $report;
    }
}
