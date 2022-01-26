<?php

namespace App\Console\Commands;

use App\Classes\KwikRequestsHandler;
use App\Models\KwikPickupsAndDelivery;
use App\Models\KwikTaskStatus;
use App\Models\Location;
use App\Models\OrderRequest;
use App\Models\OrderRequestType;
use App\Models\OrdersStatus;
use App\Models\TempOrderRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GetAllExistingKwikOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_all_existing_kwik_orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all existing KWIK orders';

    protected $processingReport;

    protected $optionsFilePath;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->optionsFilePath = 'kwik_job_query_options.json';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->getKwikOrdersProcessingOptions();
        $page = ($options->current_page - 1);
        $skipValue = $page * $options->limit;

        $response = (new KwikRequestsHandler())->listJobs($options->limit, $skipValue);
        if($response['status']){
            $responseData = $response['data'];
            $options->total_count = $responseData['count'];
            $ordersFound = $responseData['orders'];
            $this->processingReport = (object)[
                'orders_found' => count($ordersFound),
                'processed_count' => 0,
                'failed' => 0,
                'errors' => []
            ];
            if($options->total_count && $options->total_count == $options->total_processed){
                $this->info('Processed all existing kwik jobs');
                $this->updateKwikOrdersProcessingOptions($options->total_count, $options->total_processed, $options->limit);
                return 'FINISHED';
            }

            $locations = Location::all();
            $jsonErrorPayload = [];

            foreach ($ordersFound as $kwikOrder) {
                $requestType = $locations->where('name', $kwikOrder['sender_name'])->isNotEmpty() ? 'delivery' : 'pickup';
                if ($requestType === 'pickup') {
                    $customerName = $kwikOrder['sender_name'];
                } else {
                    $customerName = $kwikOrder['receiver_name'];
                }
                $kwikOrderDate = Carbon::createFromFormat('H:i  d/n/Y', $kwikOrder['started_datetime'])->toDateString();
                // Get temporary order request that fits the kwik order
                $tempOrderRequest = TempOrderRequest::where('unique_order_id', $kwikOrder['unique_order_id'])
                    ->orWhere(function (Builder $query) use ($requestType, $customerName, $kwikOrderDate, $kwikOrder) {
                        $query->where('request_type', $requestType)
                            ->where("{$requestType}_name", $customerName)
                            ->whereRaw('DATE(created_at) in (?,?)', [$kwikOrderDate, $kwikOrder['date']])
                            ->where('actual_estimate', $kwikOrder['total_amount']);
                    })->latest()->first();
                if(empty($tempOrderRequest)) {
                    $dates = [$kwikOrderDate, $kwikOrder['date']];
                    $totalAmount = $kwikOrder['total_amount'];
                    $errMessage = 'No temp_order_request found';
                    array_push(
                        $jsonErrorPayload,
                        compact('errMessage', 'customerName', 'totalAmount', 'requestType', 'kwikOrderDate', 'dates', 'kwikOrder')
                    );
                    array_push($this->processingReport->errors, "UNIQUE_ORDER_ID:{$kwikOrder['unique_order_id']} :: Temporary order request not found");
                    continue;
                }
                // Update the unique_order_id of the temporary order request to help with future checks
                $tempOrderRequest->update(['unique_order_id' => $kwikOrder['unique_order_id']]);
                $orderRequestType = OrderRequestType::where('name', $requestType)->first();
                $orderRequest = OrderRequest::where('kwik_order_id', $kwikOrder['unique_order_id'])
                    ->orWhere(function (Builder $q) use ($orderRequestType, $tempOrderRequest){
                        $q->where('order_id', $tempOrderRequest->order_id)
                            ->where('order_request_type_id', $orderRequestType->id);
                    })->first();
                if(empty($orderRequest)){
                    if ($kwikOrder['job_status'] !== KwikTaskStatus::CANCELED) {
                        $errMessage = 'No ORDER REQUEST found';
                        array_push(
                            $jsonErrorPayload,
                            compact('errMessage','customerName', 'totalAmount', 'requestType', 'kwikOrderDate', 'dates', 'kwikOrder')
                        );
                        $this->processingReport->failed += 1;
                        array_push($this->processingReport->errors, "UNIQUE_ORDER_ID:{$kwikOrder['unique_order_id']} :: TEMP_ORDER_REQUEST:{$tempOrderRequest->id} :: Order request not found");
                    } else {
                        $this->processingReport->processed_count += 1;
                    }
                    continue;
                }
                $orderRequest->update([
                    'kwik_order_id' => $kwikOrder['unique_order_id'],
                    'order_id' => $orderRequest->order_id ?? $tempOrderRequest->order_id,
                    'temp_order_request_id' => $tempOrderRequest->id
                ]);
                $this->populateKwikOrdersTable($orderRequest);
            }

            $newJsonString = json_encode($jsonErrorPayload, JSON_PRETTY_PRINT);
            file_put_contents(
                base_path('existing_kwik_orders.json'),
                stripslashes($newJsonString)
            );

            $this->info(json_encode($this->processingReport));
            if($this->processingReport->orders_found < $options->limit) {
                $options->total_processed = $options->total_count;
            } else {
                $options->total_processed += $this->processingReport->orders_found;
            }
            $this->updateKwikOrdersProcessingOptions($options->total_count, $options->total_processed, $options->limit);
            return true;
        }
        return false;
    }

    /**
     * @param OrderRequest|Model $orderRequest
     * @return bool
     */
    public function populateKwikOrdersTable($orderRequest)
    {
        try {
            $order = $orderRequest->order;
            $response = (new KwikRequestsHandler())->getJobStatus($orderRequest->kwik_order_id);
            if ($response['status']) {
                $responseData = $response['data'];
                $pickupTask = collect($responseData['orders'])->where('job_type', 0)->first();
                $deliveryTask = collect($responseData['orders'])->where('job_type', 1)->first();
                $completedDatetime = $deliveryTask['completed_datetime'] ?? $pickupTask['completed_datetime'];
                if($completedDatetime && !str_contains($completedDatetime, '00/0/0000')) {
                    $jobDateTime = Carbon::createFromFormat('H:i  d/n/Y', $completedDatetime);
                } else{
                    $completedDatetime = null;
                }
                $entry = KwikPickupsAndDelivery::updateOrCreate([
                    'unique_order_id' => $orderRequest->kwik_order_id,
                ], [
                    'user_id' => $orderRequest->user_id,
                    'order_id' => $orderRequest->order_id,
                    'order_request_id' => $orderRequest->id,
                    'job_status' => $orderRequest->matchRequestStatusToKwikStatus($pickupTask['job_status'], $deliveryTask['job_status']),
                    'credits' => $pickupTask['credits'],
                    'vehicle_id' => $responseData['vehicle_id'],
                    'date' => $completedDatetime ? $jobDateTime->toDateString(): null,
                    'total_amount' => $responseData['total_amount'],
                    'actual_amount_paid' => $orderRequest->amount,
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
                    'started_datetime' => $orderRequest->created_at,
                    'completed_datetime' => $completedDatetime ? $jobDateTime->toDateTimeString() : null,
                    'is_paid_for' => ($order ? ($order->status == OrdersStatus::COMPLETED) : false)
                ]);
                if(!in_array($entry->delivery_task_status, [
                    KwikTaskStatus::UPCOMING,
                    KwikTaskStatus::STARTED,
                    KwikTaskStatus::ARRIVED,
                    KwikTaskStatus::UNASSIGNED,
                    KwikTaskStatus::ACCEPTED
                ])){
                    $this->processingReport->processed_count += 1;
                }
            }
            return true;
        } catch (\Exception $exception) {
            $this->processingReport->failed += 1;
            array_push($this->processingReport->errors, [
                "UNIQUE_ORDER_ID:{$orderRequest->kwik_order_id} |ORDER_REQUEST: {$orderRequest->id} |MSG: {$exception->getMessage()} |FILE: {$exception->getFile()} |LINE: {$exception->getLine()}"
            ]);
            logCriticalError('Error while running \App\Console\Commands\GetAllExistingKwikOrders::populateKwikOrdersTable', $exception);
            return false;
        }
    }

    function updateKwikOrdersProcessingOptions($totalOrders, $orderProcessed, $recordsPerPage)
    {
        $currentPage = $orderProcessed > $recordsPerPage ? (int)ceil($orderProcessed/$recordsPerPage) : 1;
        $options = [
            "total_count" => $totalOrders,
            "total_processed" => $orderProcessed,
            "limit" => $recordsPerPage,
            "current_page" => $currentPage
        ];
        file_put_contents(
            base_path($this->optionsFilePath),
            stripslashes(
                json_encode($options, JSON_PRETTY_PRINT)
            )
        );
    }

    /**
     * Get kwik orders processing options
     *
     * @return mixed|object
     */
    function getKwikOrdersProcessingOptions()
    {
        if(file_exists($this->optionsFilePath)) {
            $options = json_decode(
                file_get_contents(base_path($this->optionsFilePath))
            );
        } else {
            $options = (object)[
                "total_count" => null,
                "total_processed" => 0,
                "limit" => 100,
                "current_page" => 0
            ];
        }
        $options->current_page += 1;
        return $options;
    }
}
