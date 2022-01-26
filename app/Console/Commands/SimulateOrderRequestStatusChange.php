<?php

namespace App\Console\Commands;

use App\Classes\KwikRequestsHandler;
use App\Models\OrderRequest;
use App\Models\OrderRequestStatus;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SimulateOrderRequestStatusChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order_request:simulate_status_change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tasks to simulate the various statuses of an order request';

    protected $client;
    protected $baseUrl;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = url('/') . '/api/v2/job-status-webhook';
        $this->client = new Client();

    }

    /**
     *
     * Execute the console command.
     *
     * @return bool
     * @throws GuzzleException
     */
    public function handle()
    {
        $requestStatuses = [
            OrderRequestStatus::PICKUP_REQUESTED,
            OrderRequestStatus::PICKUP_STARTED,
            OrderRequestStatus::PICKED_UP,
            OrderRequestStatus::DELIVERY_REQUESTED,
            OrderRequestStatus::DELIVERY_STARTED,
            OrderRequestStatus::PICKED_UP_FOR_DELIVERY
        ];
        $timelineOrder = [
            KwikRequestsHandler::KW_TASK_STATUS_UPCOMING,
            KwikRequestsHandler::KW_TASK_STATUS_ACCEPTED,
            KwikRequestsHandler::KW_TASK_STATUS_STARTED,
            KwikRequestsHandler::KW_TASK_STATUS_ARRIVED,
            KwikRequestsHandler::KW_TASK_STATUS_ENDED
        ];
        $pendingRequests = OrderRequest::whereIn('order_request_status_id', $requestStatuses)
            ->where('time', '<=', now())
            ->limit(100)->get();
        if($pendingRequests->count() === 0){
            return false;
        }
        Log::info("Found {$pendingRequests->count()} order requests to process");
        foreach ($pendingRequests as $orderRequest){
            try {
                $uniqueOrderId = $orderRequest->kwik_order_id;
                $accessToken = config('kwikdelivery.access_token');
                if (is_null($orderRequest->temp_next_pickup_job_status)){
                    $pickupJobStatus = KwikRequestsHandler::KW_TASK_STATUS_UPCOMING;
                    $deliveryJobStatus = KwikRequestsHandler::KW_TASK_STATUS_UPCOMING;
                } else {
                    $pickupJobStatus = $orderRequest->temp_next_pickup_job_status;
                    $deliveryJobStatus = $orderRequest->temp_next_delivery_job_status ?? 0;
                }

                $pickupJobStatusIndex = array_search($pickupJobStatus, $timelineOrder);
                $deliveryJobStatusIndex = array_search($deliveryJobStatus, $timelineOrder);

                if($pickupJobStatus === KwikRequestsHandler::KW_TASK_STATUS_ENDED){
                    $nextPickupJobStatusIndex = $pickupJobStatusIndex;
                    $nextDeliveryJobStatusIndex = (!is_null($deliveryJobStatusIndex) && $deliveryJobStatusIndex !== false) ? $deliveryJobStatusIndex + 1 : null;
                } else {
                    $nextPickupJobStatusIndex = (!is_null($pickupJobStatusIndex) && $pickupJobStatusIndex !== false) ? $pickupJobStatusIndex + 1 : null;
                    $nextDeliveryJobStatusIndex = $deliveryJobStatusIndex;
                }
                $nextPickupJobStatus = (!is_null($nextPickupJobStatusIndex) && ($nextPickupJobStatusIndex <= count($timelineOrder))) ? $timelineOrder[$nextPickupJobStatusIndex] : null;
                $nextDeliveryJobStatus = (!is_null($nextDeliveryJobStatusIndex) && ($nextDeliveryJobStatusIndex <= count($timelineOrder))) ? $timelineOrder[$nextDeliveryJobStatusIndex] : null;
                $query = [
                    "unique_order_id" => $uniqueOrderId,
                    "access_token" => $accessToken,
                    "pickup_job_status" => $pickupJobStatus,
                    "delivery_job_status" => $deliveryJobStatus,
                ];
                Log::info(json_encode(compact('query', 'pickupJobStatus', 'deliveryJobStatus', 'pickupJobStatusIndex','deliveryJobStatusIndex',
                    'nextPickupJobStatusIndex', 'nextDeliveryJobStatusIndex', 'nextPickupJobStatus', 'nextDeliveryJobStatus')));
                if(config('app.env') === 'local'){
                    $resolve = array(sprintf(
                        "%s:%d:%s",
                        config('app.domain'),
                        80,
                        '127.0.0.1'
                    ));
                    $getResponse = $this->client->request('GET', $this->baseUrl, [
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],
                        'query' => $query,
                        'curl' => [
                            CURLOPT_RESOLVE => $resolve,
                        ]
                    ]);
                }else{
                    $getResponse = $this->client->request('GET', $this->baseUrl, [
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],
                        'query' => $query,
                    ]);
                }
                $statusCode = $getResponse->getStatusCode();
                Log::info("Send request to {$this->baseUrl} and received {$statusCode} response");

                if($statusCode == 200){
                    $orderRequest->update([
                        'temp_next_pickup_job_status' => $nextPickupJobStatus,
                        'temp_next_delivery_job_status' => $nextDeliveryJobStatus
                    ]);
                }
                continue;
            }catch (ClientException $e){
                logCriticalError('Error occurred while sending guzzle request', $e);
                continue;
            } catch (\Exception $e) {
                logCriticalError('An error occurred', $e);
            }
        }
        return true;
    }
}
