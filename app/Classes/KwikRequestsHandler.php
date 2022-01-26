<?php

namespace App\Classes;

use App\Jobs\CreateOrUpdateKwikPickupsAndDeliveries;
use App\Models\KwikPickupsAndDelivery;
use App\Models\KwikTaskStatus;
use App\Models\OrderRequestStatus;
use App\Models\TempOrderRequest;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class KwikRequestsHandler {

    const ERR_PARAMETER_MISSING = 100;
    const ERR_INVALID_KEY = 101;
    const ERR_ACTION_COMPLETE = 200;
    const ERR_SHOW_ERROR_MESSAGE = 201;
    const ERR_ERROR_IN_EXECUTION = 404;

    const KW_TASK_STATUS_UPCOMING = 0;
    const KW_TASK_STATUS_STARTED = 1;
    const KW_TASK_STATUS_ENDED = 2;
    const KW_TASK_STATUS_FAILED = 3;
    const KW_TASK_STATUS_ARRIVED = 4;
    const KW_TASK_STATUS_UNASSIGNED = 6;
    const KW_TASK_STATUS_ACCEPTED = 7;
    const KW_TASK_STATUS_DECLINE = 8;
    const KW_TASK_STATUS_CANCEL = 9;
    const KW_TASK_STATUS_DELETED = 10;

    const IS_MULTIPLE_TASKS = 1;
    const LAYOUT_TYPE = 0;
    const FLEET_ID = "";
    const HAS_PICKUP = 1;
    const HAS_DELIVERY = 1;
    const PICKUP_DELIVERY_RELATIONSHIP = 0;
    const AUTO_ASSIGNMENT = 1;
    const TIMEZONE_IN_UTC_MINUTES = "+60";
    const METHOD_OF_PAYMENT_LOCAL = 32;
    const METHOD_OF_PAYMENT = 524288;
    const FORM_ID = 1;

    protected $domain_name;
    protected $access_token;
    protected $vendor_id;
    protected $baseApi;
    protected $client;
    protected $kwikUserId;
    public function __construct()
    {
        $this->access_token = config('kwikdelivery.access_token');
        $this->domain_name = config('kwikdelivery.domain_name');
        $this->vendor_id = config('kwikdelivery.vendor_id');
        $this->baseApi = config('kwikdelivery.base_url');
        $this->kwikUserId = config('kwikdelivery.user_id');
        $this->client = new Client();
    }

    public function getPriceEstimate($data)
    {
        //POST
        $url = '/get_bill_breakdown';
    }

    public function calculatePricing($data)
    {
        $url = '/send_payment_for_task';
        $requestData = [
            "custom_field_template" => "pricing-template", //Ask
            "access_token" => $this->access_token,
            "domain_name" => $this->domain_name,
            "vendor_id" => $this->vendor_id,
            "is_multiple_tasks" => self::IS_MULTIPLE_TASKS,
            "layout_type" => self::LAYOUT_TYPE,
            "pickup_custom_field_template" => "pricing-template",
            "has_pickup" => self::HAS_PICKUP,
            "has_delivery" => self::HAS_DELIVERY,
            "user_id" => $this->kwikUserId,
            "deliveries" => [
                $data['delivery']
            ],
            "pickups" => [
                $data['pickup']
            ],
            "payment_method" => isLocalOrDev() ? self::METHOD_OF_PAYMENT_LOCAL :self::METHOD_OF_PAYMENT,
	        "form_id" => self::FORM_ID,
        ];
        $response= $this->sendPostRequest($url, $requestData);
        if($response['status'] === false) {
            return $response;
        }
        $responseData = $response['data'];

        /**
         * I need to get the task cost to the nearest “upper” 10. round() will round it up or down and adding 5 will ensure I always get the upper figure
         * Hence;
         *  case 34:
         *      round(34, -1) = 30
         *      round(34 + 5, -1) = 40
         *  case 36:
         *      round(36, -1) = 40
         *      round(36 + 5, -1) = 40
         *  case 40.5:
         *      round(40.5, -1) = 40
         *      round(40.5 + 5, -1) = 50
         */
        $taskCost = round($responseData['per_task_cost'] + 5, -1);
        return [
            'status' => true,
            'amount' => [
                'rounded' => $taskCost,
                'actual' => $responseData['per_task_cost']
            ]
        ];
    }

    /**
     * @param array $data
     * @param TempOrderRequest|Model $tempOrderRequest
     * @return array
     */
    public function createTask($data, $tempOrderRequest)
    {
        //POST
        $url = '/create_task_via_vendor';
        $requestData = [
            "domain_name" => $this->domain_name,
            "access_token" => $this->access_token,
            "vendor_id" => $this->vendor_id,
            "is_multiple_tasks" => self::IS_MULTIPLE_TASKS,
            "timezone" => self::TIMEZONE_IN_UTC_MINUTES,
            "layout_type" => self::LAYOUT_TYPE,
            "has_pickup" => self::HAS_DELIVERY,
            "has_delivery" => self::HAS_DELIVERY,
            "pickup_delivery_relationship" => self::PICKUP_DELIVERY_RELATIONSHIP,
            "auto_assignment" => self::AUTO_ASSIGNMENT,
            "team_id" => "",
            "deliveries" => [
                $data['delivery']
            ],
            "pickups" => [
                $data['pickup']
            ],
            "insurance_amount" => 0,
            "total_no_of_tasks" => 1,
            "payment_method" => isLocalOrDev() ? self::METHOD_OF_PAYMENT_LOCAL :self::METHOD_OF_PAYMENT,
            "amount" => $data['amount'],
            "total_service_charge" => 0,
        ];
        if(!Carbon::parse($data['pickup']['time'])->isToday()){
            $requestData['is_schedule_task'] = 1;
        }
        $response = $this->sendPostRequest($url, $requestData);
        if($response['status'] === false) {
            return $response;
        }
        $responseData = $response['data'];
        $tempOrderRequest->update([
            'unique_order_id' => $responseData['unique_order_id']
        ]);

        if ($tempOrderRequest->order_id){
            CreateOrUpdateKwikPickupsAndDeliveries::dispatch($tempOrderRequest, KwikTaskStatus::UPCOMING, KwikTaskStatus::UPCOMING)->delay(now()->addMinutes(5));
        }

        return [
            'status' => true,
            'data' => $responseData
        ];
    }

    public function cancelTask($kwikJobIds)
    {
        //POST
        $url = '/cancel_vendor_task';
        $requestData = [
            'access_token' => $this->access_token,
            'vendor_id' => $this->vendor_id,
            'domain_name' => $this->domain_name,
            'job_id' => $kwikJobIds,
            'job_status' => self::KW_TASK_STATUS_CANCEL,
        ];
        $response = $this->sendPostRequest($url, $requestData);

        if($response['status'] === false) {
            return $response;
        }
        $responseData = $response['data'];

        return [
            'status' => true,
            'data' => $responseData
        ];

    }

    public function getJobStatus($kwikOrderID)
    {
        //GET
        $url = "/view_task_by_relationship_id?access_token=$this->access_token&unique_order_id=$kwikOrderID";
        return $this->sendGetRequest($url);

    }

    public function listJobs($limit = 20, $skip = null)
    {
        //GET
        $url = "/get_order_history_with_pagination?access_token=$this->access_token&limit=$limit&skip=$skip";
        return $this->sendGetRequest($url);
    }

    public function sendGetRequest($url, $query = null)
    {
        $fullUrl = $this->baseApi . $url;
        $log = saveExternalApiReq([
            'url' => $fullUrl,
            'method' => 'GET_EXTERNAL',
        ]);
        try {
            $get_response = $this->client->get($fullUrl, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'query' => $query
            ]);
            $requestResponse = $get_response->getBody()->getContents();
            $log->update([
                'response' => $requestResponse
            ]);
            $responseBody = json_decode($requestResponse, true);
            if($responseBody['status'] === 200){
                return [
                    'status' => true,
                    'data' => $responseBody['data'],
                    'message' => $responseBody['message'],
                ];
            }
            return [
                'status' => false,
                'message' => $responseBody['message'],
                'show_error' => ($responseBody['status'] === 201)
            ];
        }catch (\Exception $e){
            $log->update([
                'response' => $e->getMessage()
            ]);
            Log::error(json_encode($e));
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function sendPostRequest($url, $requestData)
    {
        $fullUrl = $this->baseApi . $url;
        $log = saveExternalApiReq([
            'url' => $fullUrl,
            'method' => 'POST_EXTERNAL',
            'data_param' => json_encode($requestData),
        ]);
        try {
            $get_response = $this->client->post($fullUrl, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $requestData
            ]);
            $requestResponse = $get_response->getBody()->getContents();
            $log->update([
                'response' => $requestResponse
            ]);
            Log::info($requestResponse);
            $responseBody = json_decode($requestResponse, true);
            if($responseBody['status'] === 200){
                return [
                    'status' => true,
                    'data' => $responseBody['data'],
                    'message' => $responseBody['message'],
                ];
            }
            return [
                'status' => false,
                'message' => $responseBody['message'],
                'show_error' => ($responseBody['status'] === 201)
            ];
        }catch (\Exception $e){
            $log->update([
                'response' => $e->getMessage()
            ]);
            Log::error(json_encode($e));
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

}

