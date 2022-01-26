<?php

namespace App\Http\Controllers\CustomerApi;

use App\Classes\KwikRequestsHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderRequestCollection;
use App\Jobs\CreateOrUpdateKwikPickupsAndDeliveries;
use App\Models\KwikTaskStatus;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderRequestStatus;
use App\Models\OrderRequestType;
use App\Models\OrdersTimeline;
use App\Models\TempOrderRequest;
use App\Models\UserAddress;
use App\Rules\ValidPhone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PickupAndDeliveryRequestController extends Controller {

    protected $guard = 'users';
    protected $user;

    public function __construct(){

        $this->user = $this->getAuthUser($this->guard);
    }

    public function getPickupEstimate(Request $request)
    {
        if($request->get('date_time')){
            $request->merge([
                'date_time' => date('Y-m-d H:i:s', strtotime($request->date_time))
            ]);
        }
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['bail', 'required', 'max:200'],
                'phone' => ['bail', 'required', new ValidPhone],
                'address' => ['bail', 'required'],
                'latitude' => ['bail', 'required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
                'longitude' => ['bail', 'required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
                'store_location' => ['bail', 'required', Rule::exists('locations', 'id')->whereNull('deleted_at')],
                'date_time' => [
                    'bail',
                    Rule::requiredIf(function(){
                        return (!now()->isBetween(Carbon::parse('today 9AM'), Carbon::parse('today 5PM')));
                    }),
                    'date_format:Y-m-d H:i:s',
                    function($attribute, $value, $fail) {
                        $validationResult = isValidPickupAndDeliveryTime($value);
                        if(!$validationResult['status']) {
                            $fail($validationResult['message']);
                        }
                    }
                ],
                'note' => ['nullable', 'string', 'max:200'],
            ], [
                'date_time.required' => 'Our pickup and delivery service is only available between 9am to 5pm, Monday to Friday. Please schedule the pickup',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $jobDetails = self::getJobDetails($request);
            if(!$jobDetails['status']){
                return errorResponse('Something went wrong', 500, $request);
            }
            $storeLocation = Location::find($request->store_location);
            $pickupReqHandler = new KwikRequestsHandler();
            $response = $pickupReqHandler->calculatePricing($jobDetails);

            if($response['status'] === true){
                $pickupCost = $response['amount'];
                $pickupInfo = $jobDetails['pickup'];
                $deliveryInfo = $jobDetails['delivery'];
                $tmpRequest = TempOrderRequest::create([
                    'user_id' => $this->user->id,
                    'location_id' => $storeLocation->id,

                    'pickup_name' => $pickupInfo['name'],
                    'pickup_address' => $pickupInfo['address'],
                    'pickup_latitude' => $pickupInfo['latitude'],
                    'pickup_longitude' => $pickupInfo['longitude'],
                    'pickup_time' => $pickupInfo['time'],
                    'pickup_phone' => $pickupInfo['phone'],

                    'delivery_name' => $deliveryInfo['name'],
                    'delivery_address' => $deliveryInfo['address'],
                    'delivery_latitude' => $deliveryInfo['latitude'],
                    'delivery_longitude' => $deliveryInfo['longitude'],
                    'delivery_time' => $deliveryInfo['time'],
                    'delivery_phone' => $deliveryInfo['phone'],

                    'request_type' => TempOrderRequest::PICKUP_TYPE,
                    'note' => $request->note,
                    'amount' => $pickupCost['rounded'],
                    'actual_estimate' => $pickupCost['actual'],
                    'accepted' => ($request->has('date_time'))
                ]);
                return successResponse('Successful', [
                    'amount' => $pickupCost['rounded'],
                    'temp_pickup_request_id' => $tmpRequest->id
                ], $request);
            }

            if($response['show_error'] === true){
                return successResponse($response['message'], null, $request, false);
            }
            return errorResponse($response['message'], 400, $request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmPickupRequest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'accepted' => ['required', 'boolean'],
                'temp_pickup_request_id' => ['required', Rule::exists('temp_order_requests', 'id')
                    ->where('user_id', $this->user->id)]
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $tempPickupRequest = TempOrderRequest::find($request->temp_pickup_request_id);
            if($tempPickupRequest->accepted && $tempPickupRequest->unique_order_id){
                return successResponse('Successful', null, $request);
            }

            if($request->accepted === false){
                $tempPickupRequest->update(['accepted' => false]);
                /*
                Todo:: Decide if the temporary request should be deleted so as not to have redundant data on the db
                */
                return successResponse('Pickup request cancelled', null, $request);
            }
            $jobDetails = [
                "pickup" => [
                    "address" => $tempPickupRequest->pickup_address,
                    "name" => $tempPickupRequest->pickup_name,
                    "latitude" => $tempPickupRequest->pickup_latitude,
                    "longitude" => $tempPickupRequest->pickup_longitude,
                    "time" => $tempPickupRequest->pickup_time,
                    "phone" => $tempPickupRequest->pickup_phone,
                    "email" => $this->user->email,
                    "template_name" => "pricing-template",
                    "ref_images" => ""
                ],
                "delivery" => [
                    "address" => $tempPickupRequest->delivery_address,
                    "name" => $tempPickupRequest->delivery_name,
                    "latitude" => $tempPickupRequest->delivery_latitude,
                    "longitude" => $tempPickupRequest->delivery_longitude,
                    "time" => $tempPickupRequest->delivery_time,
                    "phone" => $tempPickupRequest->delivery_phone,
                    "has_return_task" => false,
                    "is_package_insured" => 0,
                    "template_name" => "pricing-template",
                    "ref_images" => "",
                    "hadVairablePayment" => 0,
                    "hadFixedPayment" => 1,
                ],
                "amount" => $tempPickupRequest->actual_estimate
            ];
            $pickupReqHandler = new KwikRequestsHandler();
            $jobDetails['order_request_status_id'] = OrderRequestStatus::PICKUP_REQUESTED;
            $response = $pickupReqHandler->createTask($jobDetails, $tempPickupRequest);
            if($response['status'] === false){
                if(isset($response['show_error']) && $response['show_error'] === true){
                    //Show error message to user
                    return successResponse($response['message'], null, $request, false);
                }

                return errorResponse('An error occurred', 400, $request);
            }
            $responseData = $response['data'];
            if(isset($responseData['pickups']) && !empty($responseData['pickups'])) {
                $jobPickup = $responseData['pickups'][0];
                $jobDelivery = $responseData['deliveries'][0];
                $address = UserAddress::firstOrCreate([
                    'user_id' => $this->user->id,
                    'latitude' => $tempPickupRequest->pickup_latitude,
                    'longitude' => $tempPickupRequest->pickup_longitude,
                ],[
                    'address' => $tempPickupRequest->pickup_address
                ]);
                OrderRequest::create([
                    'amount' => $tempPickupRequest->amount,
                    'actual_estimate' => $tempPickupRequest->actual_estimate,
                    'user_id' => $this->user->id,
                    'name' => $tempPickupRequest->pickup_name,
                    'phone' => $tempPickupRequest->pickup_phone,
                    'order_id' => null,
                    'address_id' => $address->id,
                    'location_id' => $tempPickupRequest->location_id,
                    'time' => $tempPickupRequest->pickup_time,
                    'order_request_type_id' => OrderRequestType::PICKUP,
                    'note' => $tempPickupRequest->note,
                    'scheduled' => $tempPickupRequest->scheduled,
                    'order_request_status_id' => OrderRequestStatus::PICKUP_REQUESTED,
                    'kwik_job_ids' => $jobPickup['job_id'] . ',' . $jobDelivery['job_id'],
                    'kwik_order_id' => (isset($responseData['unique_order_id']) && !empty($responseData['unique_order_id']))
                        ? $responseData['unique_order_id']: null,
                    'temp_order_request_id' => $tempPickupRequest->id
                ]);
                Queue::push(
                    new CreateOrUpdateKwikPickupsAndDeliveries($tempPickupRequest, KwikTaskStatus::UPCOMING, KwikTaskStatus::UPCOMING)
                );

                return successResponse('Successful', null, $request);
            }
            throw new \Exception('Malformed task response');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function getDeliveryEstimate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['bail', 'required', 'max:200'],
                'phone' => ['bail', 'required', new ValidPhone],
                'address' => ['bail', 'required'],
                'latitude' => ['bail', 'required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
                'longitude' => ['bail', 'required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
                'order_id' => ['bail', 'required', 'exists:orders,id', function($attribute, $value, $fail) {
                    $orderBelongsToUser = $this->user->orders()->where('id', $value)->exists();
                    if(!$orderBelongsToUser){
                        $fail("Invalid $attribute provided");
                    }
                }],
                'date_time' => [
                    'bail',
                    'nullable',
                    Rule::requiredIf(function(){
                        return (!now()->isBetween(Carbon::parse('today 9AM'), Carbon::parse('today 5PM')));
                    }),
                    'date_format:Y-m-d H:i:s',
                    function($attribute, $value, $fail) {
                        $validationResult = isValidPickupAndDeliveryTime($value);
                        if(!$validationResult['status']) {
                            $fail($validationResult['message']);
                        }
                    }
                ],
                'note' => ['nullable', 'string', 'max:200'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $order = Order::find($request->order_id);
            if($order->hasDeliveryRequest(true)){
                return errorResponse('A delivery request already exists for this order', 400, $request);
            }
            $jobDetails = self::getJobDetails($request, $order);
            if(!$jobDetails['status']){
                return errorResponse('Something went wrong', 500, $request);
            }
            $storeLocation = $order->location;
            $pickupReqHandler = new KwikRequestsHandler();
            $response = $pickupReqHandler->calculatePricing($jobDetails);

            if($response['status'] === true){
                $pickupCost = $response['amount'];
                $pickupInfo = $jobDetails['pickup'];
                $deliveryInfo = $jobDetails['delivery'];
                $tmpRequest = TempOrderRequest::create([
                    'user_id' => $this->user->id,
                    'location_id' => $storeLocation->id,
                    'order_id' => $order->id,

                    'pickup_name' => $pickupInfo['name'],
                    'pickup_address' => $pickupInfo['address'],
                    'pickup_latitude' => $pickupInfo['latitude'],
                    'pickup_longitude' => $pickupInfo['longitude'],
                    'pickup_time' => $pickupInfo['time'],
                    'pickup_phone' => $pickupInfo['phone'],

                    'delivery_name' => $deliveryInfo['name'],
                    'delivery_address' => $deliveryInfo['address'],
                    'delivery_latitude' => $deliveryInfo['latitude'],
                    'delivery_longitude' => $deliveryInfo['longitude'],
                    'delivery_time' => $deliveryInfo['time'],
                    'delivery_phone' => $deliveryInfo['phone'],

                    'request_type' => TempOrderRequest::DELIVERY_TYPE,
                    'note' => $request->note,
                    'amount' => $pickupCost['rounded'],
                    'actual_estimate' => $pickupCost['actual'],
                    'accepted' => ($request->has('date_time'))
                ]);
                return successResponse('Successful', [
                    'amount' => $pickupCost['rounded'],
                    'temp_delivery_request_id' => $tmpRequest->id
                ], $request);
            }

            if($response['show_error'] === true){
                return successResponse($response['message'], null, $request, false);
            }
            return errorResponse($response['message'], 400, $request);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function confirmDeliveryRequest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'accepted' => ['required', 'boolean'],
                'temp_delivery_request_id' => ['required', Rule::exists('temp_order_requests', 'id')
                    ->where('user_id', $this->user->id)]
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $tempDeliveryRequest = TempOrderRequest::find($request->temp_delivery_request_id);

            // Ensure the user has accepted the fee'
            if($request->accepted === false){
                $tempDeliveryRequest->update(['accepted' => false]);
                return successResponse('Delivery request cancelled', null, $request);
            }
            $tempDeliveryRequest->update(['accepted' => true]);
            $address = UserAddress::firstOrCreate([
                'user_id' => $this->user->id,
                'latitude' => $tempDeliveryRequest->delivery_latitude,
                'longitude' => $tempDeliveryRequest->delivery_longitude,
            ],[
                'address' => $tempDeliveryRequest->delivery_address
            ]);
            $order = Order::find($tempDeliveryRequest->order_id);
            $jobDetails = [
                "pickup" => [
                    "address" => $tempDeliveryRequest->pickup_address,
                    "name" => $tempDeliveryRequest->pickup_name,
                    "latitude" => $tempDeliveryRequest->pickup_latitude,
                    "longitude" => $tempDeliveryRequest->pickup_longitude,
                    "time" => $tempDeliveryRequest->pickup_time,
                    "phone" => $tempDeliveryRequest->pickup_phone,
                    "email" => $this->user->email,
                    "template_name" => "pricing-template",
                    "ref_images" => ""
                ],
                "delivery" => [
                    "address" => $tempDeliveryRequest->delivery_address,
                    "name" => $tempDeliveryRequest->delivery_name,
                    "latitude" => $tempDeliveryRequest->delivery_latitude,
                    "longitude" => $tempDeliveryRequest->delivery_longitude,
                    "time" => $tempDeliveryRequest->delivery_time,
                    "phone" => $tempDeliveryRequest->delivery_phone,
                    "has_return_task" => false,
                    "is_package_insured" => 0,
                    "template_name" => "pricing-template",
                    "ref_images" => "",
                    "hadVairablePayment" => 0,
                    "hadFixedPayment" => 1,
                ],
                "amount" => $tempDeliveryRequest->actual_estimate

            ];
            $orderRequest = OrderRequest::create([
                'amount' => $tempDeliveryRequest->amount,
                'actual_estimate' => $tempDeliveryRequest->actual_estimate,
                'user_id' => $this->user->id,
                'name' => $tempDeliveryRequest->delivery_name,
                'phone' => $tempDeliveryRequest->delivery_phone,
                'order_id' => $tempDeliveryRequest->order_id,
                'address_id' => $address->id,
                'location_id' => $tempDeliveryRequest->location_id,
                'time' => $tempDeliveryRequest->pickup_time,
                'order_request_type_id' => OrderRequestType::DELIVERY,
                'note' => $tempDeliveryRequest->note,
                'scheduled' => $tempDeliveryRequest->scheduled,
                'order_request_status_id' => null,
                'kwik_job_ids' =>null,
                'kwik_order_id' => null,
                'has_pickup' => $order->hasPickupRequest(),
                'job_details_payload' => json_encode($jobDetails),
                'temp_order_request_id' => $tempDeliveryRequest->id
            ]);
            $order->update([
                'delivery_cost' => $orderRequest->amount
            ]);


            return successResponse('Successful', null, $request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }


    public function confirmDeliveryRequestOld(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'accepted' => ['required', 'boolean'],
                'temp_delivery_request_id' => ['required', Rule::exists('temp_order_requests', 'id')
                    ->where('user_id', $this->user->id)]
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $tempPickupRequest = TempOrderRequest::find($request->temp_delivery_request_id);

            // Ensure the user has accepted the fee'
            if($request->accepted === false){
                $tempPickupRequest->update(['accepted' => false]);
                return successResponse('Delivery request cancelled', null, $request);
            }
            $order = Order::find($tempPickupRequest->order_id);
            $jobDetails = [
                "pickup" => [
                    "address" => $tempPickupRequest->pickup_address,
                    "name" => $tempPickupRequest->pickup_name,
                    "latitude" => $tempPickupRequest->pickup_latitude,
                    "longitude" => $tempPickupRequest->pickup_longitude,
                    "time" => $tempPickupRequest->pickup_time,
                    "phone" => $tempPickupRequest->pickup_phone,
                    "email" => $this->user->email,
                    "template_name" => "pricing-template",
                    "ref_images" => ""
                ],
                "delivery" => [
                    "address" => $tempPickupRequest->delivery_address,
                    "name" => $tempPickupRequest->delivery_name,
                    "latitude" => $tempPickupRequest->delivery_latitude,
                    "longitude" => $tempPickupRequest->delivery_longitude,
                    "time" => $tempPickupRequest->delivery_time,
                    "phone" => $tempPickupRequest->delivery_phone,
                    "has_return_task" => false,
                    "is_package_insured" => 0,
                    "template_name" => "pricing-template",
                    "ref_images" => "",
                    "hadVairablePayment" => 0,
                    "hadFixedPayment" => 1,
                ],
                "amount" => $tempPickupRequest->actual_estimate
            ];

            $pickupReqHandler = new KwikRequestsHandler();
            $response = $pickupReqHandler->createTask($jobDetails, $tempPickupRequest);

            if($response['status'] === false){
                if($response['show_error'] === true){
                    return successResponse($response['message'], null, $request, false);
                }
                return errorResponse('An error occurred', 400, $request);
            }

            $responseData = $response['data'];

            if(isset($responseData['pickups']) && !empty($responseData['pickups'])) {
                $jobPickup = $responseData['pickups'][0];
                $jobDelivery = $responseData['deliveries'][0];
                $address = UserAddress::firstOrCreate([
                    'user_id' => $this->user->id,
                    'latitude' => $tempPickupRequest->delivery_latitude,
                    'longitude' => $tempPickupRequest->delivery_longitude,
                ],[
                    'address' => $tempPickupRequest->delivery_address
                ]);

                $orderRequest = OrderRequest::create([
                    'amount' => $tempPickupRequest->amount,
                    'actual_estimate' => $tempPickupRequest->actual_estimate,
                    'user_id' => $this->user->id,
                    'name' => $tempPickupRequest->delivery_name,
                    'phone' => $tempPickupRequest->delivery_phone,
                    'order_id' => $tempPickupRequest->order_id,
                    'address_id' => $address->id,
                    'location_id' => $tempPickupRequest->location_id,
                    'time' => $tempPickupRequest->pickup_time,
                    'order_request_type_id' => OrderRequestType::DELIVERY,
                    'note' => $tempPickupRequest->note,
                    'scheduled' => $tempPickupRequest->scheduled,
                    'order_request_status_id' => OrderRequestStatus::DELIVERY_REQUESTED,
                    'kwik_job_ids' => $jobPickup['job_id'] . ',' . $jobDelivery['job_id'],
                    'kwik_order_id' => (isset($responseData['unique_order_id']) && !empty($responseData['unique_order_id']))
                        ? $responseData['unique_order_id']: null,
                    'has_pickup' => $order->hasPickupRequest(),
                ]);
                $order->update([
                    'delivery_cost' => $orderRequest->amount
                ]);
                return successResponse('Successful', null, $request);
            }
            throw new \Exception('Malformed task response');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusChangeWebHook(Request $request)
    {
        $expectedStatuses = [
            KwikTaskStatus::UPCOMING,
            KwikTaskStatus::STARTED,
            KwikTaskStatus::ENDED,
            KwikTaskStatus::FAILED,
            KwikTaskStatus::ARRIVED,
            KwikTaskStatus::UNASSIGNED,
            KwikTaskStatus::ACCEPTED,
            KwikTaskStatus::DECLINE,
            KwikTaskStatus::CANCELED,
            KwikTaskStatus::DELETED,
        ];

        try {
            $validator = Validator::make($request->all(), [
                'access_token' => 'required|in:'.config('kwikdelivery.access_token'),
                'unique_order_id' => 'required',
                'pickup_job_status' => ['required', Rule::in($expectedStatuses)],
                'delivery_job_status' => ['required', Rule::in($expectedStatuses)]
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $orderRequest = OrderRequest::where('kwik_order_id', $request->unique_order_id)->first();

            if(!empty($orderRequest)) {
                $requestStatus = $orderRequest->matchRequestStatusToKwikStatus(
                    (int)$request->pickup_job_status, (int)$request->delivery_job_status
                );

                $tempOrderRequest = TempOrderRequest::where('unique_order_id', $request->unique_order_id)->first();
                if(empty($tempOrderRequest)) {
                    Queue::push(
                        new CreateOrUpdateKwikPickupsAndDeliveries($tempOrderRequest, $request->pickup_job_status, $request->delivery_job_status)
                    );
                }
                $orderRequest->update([
                    'order_request_status_id' => $requestStatus
                ]);

                OrdersTimeline::updateOrCreate([
                    'order_request_id' => $orderRequest->id,
                    'status_id' => $requestStatus
                ],[
                    'order_id' => $orderRequest->order_id,
                ]);
            }
            return successResponse('Successful', null, $request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function getAllOrderRequests(Request $request){
        try {
            $validator = Validator::make($request->all(),  [
                'records_per_page' => 'bail|nullable|integer|max:50',
                'page' => 'bail|nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $recordsPerPage = $request->records_per_page ?? 20;
            $responseData = new OrderRequestCollection(
                OrderRequest::getUserOrderRequests($this->user->id)->paginate($recordsPerPage)
            );
            return successResponse('Successful', $responseData, $request);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function getOrderRequestCurrentLocation(Request $request)
    {
        $errorPayload = (object)[
            'message' => 'An error occurred',
            'statusCode' => 400,
            'exception' => null,
        ];

        try {
            $validator = Validator::make($request->all(), [
                'order_request_id' => 'required|exists:order_requests,id',
            ], [], [
                'order_request_id' => 'order request'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $orderRequest = OrderRequest::where('id', $request->order_request_id)->first();
            $response = (object)(new KwikRequestsHandler())->getJobStatus($orderRequest->kwik_order_id);
            if ($response->status) {
                $responseData = $response->data;
                $pickupJob = $responseData['orders'][0];
                $deliveryJob = $responseData['orders'][1];
                $data = $orderRequest->getOrderLatitudeAndLongitude($pickupJob, $deliveryJob);
                $message = 'Successful';
                $status = true;
                if(!$data['trackable']){
                    $message = (in_array($orderRequest->order_request_id, [OrderRequestStatus::PICKUP_REQUESTED , OrderRequestStatus::DELIVERY_REQUESTED]))
                        ? "No rider assigned"
                        : "Order is not trackable";
                    $status = false;
                }
                return successResponse($message, $data, $request, $status);
            }
            $errorPayload->message = ($response->show_error) ? $response->message : 'Unable to track your request at this time';
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $errorPayload->message = $errors[0];
            $errorPayload->statusCode = 400;
        } catch (\Exception $e) {
            $errorPayload->message = 'Something went wrong';
            $errorPayload->statusCode = 500;
            $errorPayload->exception = $e;
        }
        return errorResponse($errorPayload->message, $errorPayload->statusCode, $request, $errorPayload->exception);
    }

    public function cancelOrderRequest(Request $request){
        $errorPayload = (object)[
            'message' => 'An error occurred',
            'statusCode' => 400,
            'exception' => null,
        ];

        try {
            $validator = Validator::make($request->all(), [
                'order_request_id' => ['required',Rule::exists('order_requests','id')->where('user_id', $this->user->id)],
            ], [], [
                'order_request_id' => 'order request'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $orderRequest = OrderRequest::where('id', $request->order_request_id)->first();
            $requestType = $orderRequest->request_type_id;
            $startedStatus = $requestType === OrderRequestType::PICKUP ? OrderRequestStatus::PICKUP_STARTED : OrderRequestStatus::DELIVERY_STARTED;
            $cancelledStatus = $requestType === OrderRequestType::PICKUP ? OrderRequestStatus::PICKUP_CANCELED : OrderRequestStatus::DELIVERY_CANCELLED;
            if($orderRequest->statusExistsInTimeline($startedStatus)){
                return successResponse("This {$orderRequest->order_request_type->name} request has been assigned and cannot be cancelled", null, $request, false);
            }
            $response = (object)(new KwikRequestsHandler())->cancelTask($orderRequest->kwik_job_ids);
            if ($response->status) {
                $orderRequest->update([
                    'order_request_status_id' => $cancelledStatus
                ]);
                if($orderRequest->order_id){
                    $order = $orderRequest->order;
                    $col = $requestType === OrderRequestType::PICKUP ? 'pickup_cost' : 'delivery_cost';
                    $order->update([
                        $col => null,
                    ]);

                }
                return successResponse('Successful', null, $request);
            }
            $errorPayload->message = ($response->show_error) ? $response->message : "Failed to cancel {$orderRequest->order_request_type->name} request";
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $errorPayload->message = $errors[0];
            $errorPayload->statusCode = 400;
        } catch (\Exception $e) {
            $errorPayload->message = 'Something went wrong';
            $errorPayload->statusCode = 500;
            $errorPayload->exception = $e;
        }
        return errorResponse($errorPayload->message, $errorPayload->statusCode, $request, $errorPayload->exception);
    }
    /**
     * @param Request $request
     * @param Order|null $order
     * @return array
     */
    public static function getJobDetails(Request $request, Order $order = null)
    {
        try {
            if($order){
                $storeLocation = $order->location;
                $deliveryTime = Carbon::parse($request->get('date_time'))->toDateTimeString();
                return [
                    "status" => true,
                    "pickup" => [
                        "address" => $storeLocation->address,
                        "name" => $storeLocation->name,
                        "latitude" => $storeLocation->latitude,
                        "longitude" => $storeLocation->longitude,
                        "time" => $deliveryTime,
                        "phone" => $storeLocation->phone,
                    ],
                    "delivery" => [
                        "name" => $request->name,
                        "address" => $request->address,
                        "latitude" => $request->latitude,
                        "longitude" => $request->longitude,
                        "time" => Carbon::parse($deliveryTime)->addMinutes(30)->toDateTimeString(),
                        "phone" => $request->phone,
                        "email" => $request->email,
                    ],
                ];

            }
            $storeLocation = Location::find($request->store_location);
            $pickupTime = Carbon::parse($request->get('date_time'))->toDateTimeString();
            return [
                "status" => true,
                "pickup" => [
                    "name" => $request->name,
                    "address" => $request->address,
                    "latitude" => $request->latitude,
                    "longitude" => $request->longitude,
                    "time" => $pickupTime,
                    "phone" => $request->phone,
                    "email" => $request->email,
                ],
                "delivery" => [
                    "address" => $storeLocation->address,
                    "name" => $storeLocation->name,
                    "latitude" => $storeLocation->latitude,
                    "longitude" => $storeLocation->longitude,
                    "time" => Carbon::parse($pickupTime)->addMinutes(30)->toDateTimeString(),
                    "phone" => $storeLocation->phone,
                ]
            ];
        } catch (\Exception $e) {
            logCriticalError('Unable to get jobDetails', $e);
            return [
                "status" => false,
                "message" => 'Something went wrong',
            ];
        }
    }

}

