<?php

namespace App\Jobs;

use App\Classes\KwikRequestsHandler;
use App\Models\KwikPickupsAndDelivery;
use App\Models\KwikTaskStatus;
use App\Models\OrderRequest;
use App\Models\OrderRequestStatus;
use App\Models\OrderRequestType;
use App\Models\OrdersStatus;
use App\Models\TempOrderRequest;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateOrUpdateKwikPickupsAndDeliveries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tempOrderRequest;
    protected $pickupJobStatus;
    protected $deliveryJobStatus;

    /**
     * Create a new job instance.
     *
     * @param TempOrderRequest|Model $tempOrderRequest
     * @param int $pickupJobStatus
     * @param int $deliveryJobStatus
     *
     * @return void
     */
    public function __construct($tempOrderRequest, $pickupJobStatus, $deliveryJobStatus)
    {
        $this->tempOrderRequest = $tempOrderRequest;
        $this->pickupJobStatus = $pickupJobStatus;
        $this->deliveryJobStatus = $deliveryJobStatus;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var OrderRequest $orderRequest
         */
        $orderRequestType = OrderRequestType::where('name', $this->tempOrderRequest->request_type)->first();
        $orderRequestQuery = OrderRequest::where('kwik_order_id', $this->tempOrderRequest->unique_order_id)
            ->orWhere('temp_order_request_id', $this->tempOrderRequest->id);
        if (!$orderRequestQuery->exists() && $this->tempOrderRequest->order_id) {
            $orderRequestQuery->orWhere(function (Builder $q) use ($orderRequestType) {
                $q->where('order_id', $this->tempOrderRequest->order_id)
                    ->where('order_request_type_id', $orderRequestType->id);
            });
        }
        $orderRequest = $orderRequestQuery->first();
        $order = $this->tempOrderRequest->order;
        $tempReqStatus = $orderRequestType->id === OrderRequestType::PICKUP ? OrderRequestStatus::PICKUP_REQUESTED : OrderRequestStatus::DELIVERY_REQUESTED;
        $jobStatus = $orderRequest ? $orderRequest->order_request_status_id : $tempReqStatus;

        if(!empty($orderRequest)){
            $orderRequest->update([
                'kwik_order_id' => $orderRequest->kwik_order_id ?? $this->tempOrderRequest->unique_order_id,
                'temp_order_request_id' => $this->tempOrderRequest->id,
                'order_request_status_id' => $jobStatus,
                'pickup_task_status' => (int)$this->pickupJobStatus,
                'delivery_task_status' => (int)$this->deliveryJobStatus
            ]);
        }
        if (isLocalOrDev()){
            KwikPickupsAndDelivery::updateOrCreate([
                'unique_order_id' => $this->tempOrderRequest->unique_order_id,
            ], [
                'user_id' => $this->tempOrderRequest->user_id,
                'order_id' => $this->tempOrderRequest->order_id,
                'order_request_id' => $orderRequest ? $orderRequest->id : null,
                'job_status' => $jobStatus,
                'credits' => null,
                'vehicle_id' => 1,
                'date' => $this->tempOrderRequest->created_at->toDateString(),
                'total_amount' => $this->tempOrderRequest->actual_estimate,
                'actual_amount_paid' => $this->tempOrderRequest->amount,
                'is_return_task' => 0,
                'is_multiple_deliveries' => 0,
                'sender_name' => $this->tempOrderRequest->pickup_name,
                'pickup_address' => $this->tempOrderRequest->pickup_address,
                'pickup_task_status' => $orderRequest ? $orderRequest->pickup_task_status : KwikTaskStatus::UPCOMING,
                'pickup_longitude' => $this->tempOrderRequest->pickup_longitude,
                'pickup_latitude' => $this->tempOrderRequest->pickup_latitude,
                'receiver_name' => $this->tempOrderRequest->delivery_name,
                'delivery_address' => $this->tempOrderRequest->delivery_address,
                'delivery_task_status' => $orderRequest ? $orderRequest->delivery_task_status : KwikTaskStatus::UPCOMING,
                'delivery_longitude' => $this->tempOrderRequest->delivery_longitude,
                'delivery_latitude' => $this->tempOrderRequest->delivery_latitude,
                'started_datetime' => $orderRequest ? $orderRequest->created_at : $this->tempOrderRequest->created_at,
                'completed_datetime' => $orderRequest ? $orderRequest->updated_at : $this->tempOrderRequest->updated_at,
                'is_paid_for' => ($this->tempOrderRequest->order_id ? ($order->status == OrdersStatus::COMPLETED) : false)
            ]);
        } else {
            $response = (new KwikRequestsHandler())->getJobStatus($this->tempOrderRequest->unique_order_id);
            if ($response['status']) {
                $responseData = $response['data'];
                $pickupTask = collect($responseData['orders'])->where('job_type', 0)->first();
                $deliveryTask = collect($responseData['orders'])->where('job_type', 1)->first();

                $completedDatetime = $deliveryTask['completed_datetime'] ?? $pickupTask['completed_datetime'];
                $startedDatetime = $orderRequest->created_at;
                if ($completedDatetime && !str_contains($completedDatetime, '00/0/0000')) {
                    $jobDateTime = Carbon::createFromFormat('H:i  d/n/Y', $completedDatetime);
                } else {
                    $completedDatetime = null;
                }
                KwikPickupsAndDelivery::updateOrCreate([
                    'unique_order_id' => $this->tempOrderRequest->unique_order_id,
                ], [
                    'user_id' => $this->tempOrderRequest->user_id,
                    'order_id' => $this->tempOrderRequest->order_id,
                    'order_request_id' => $orderRequest ? $orderRequest->id : null,
                    'job_status' => $jobStatus,
                    'credits' => $pickupTask['credits'],
                    'vehicle_id' => $responseData['vehicle_id'],
                    'date' => $completedDatetime ? $jobDateTime->toDateString() : null,
                    'total_amount' => $responseData['total_amount'],
                    'actual_amount_paid' => $this->tempOrderRequest->amount,
                    'is_return_task' => $pickupTask['is_return_task'],
                    'is_multiple_deliveries' => count($responseData['orders']) > 2,
                    'sender_name' => $pickupTask['name'],
                    'pickup_address' => $pickupTask['address'],
                    'pickup_task_status' => $pickupTask['job_status'],
                    'pickup_longitude' => $pickupTask['job_longitude'],
                    'pickup_latitude' => $pickupTask['job_latitude'],
                    'receiver_name' => $deliveryTask['name'],
                    'delivery_address' => $deliveryTask['address'],
                    'delivery_task_status' => $deliveryTask['job_status'],
                    'delivery_longitude' => $deliveryTask['job_longitude'],
                    'delivery_latitude' => $deliveryTask['job_latitude'],
                    'started_datetime' => $startedDatetime ? $startedDatetime->toDateTimeString() : null,
                    'completed_datetime' => $completedDatetime ? $jobDateTime->toDateTimeString() : null,
                    'is_paid_for' => ($order ? ($order->status == OrdersStatus::COMPLETED) : false)
                ]);
            }
        }

    }
}
