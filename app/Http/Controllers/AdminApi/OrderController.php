<?php

namespace App\Http\Controllers\AdminApi;

use App\Classes\Meta;
use App\Classes\OrderRequestHandler;
use App\Http\Resources\LockerResource;
use App\Http\Resources\OrderResource;
use App\Mail\DropoffOrderCreated;
use App\Models\Locker;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderRequestType;
use App\Models\OrdersLocker;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $guard = 'admins';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $admin;
    protected $authLocation;
    public function __construct()
    {
        $this->middleware('permission:list_services', ['only' => ['getAllServices']]);
        $this->middleware('permission:create_order', ['only' => ['createSelfServiceOrder']]);
        $this->middleware('permission:create_dropoff_order', ['only' => ['createDropOffOrder']]);
        $this->middleware('permission:edit_order', ['only' => ['updateOrder', 'markOrderAsCollected']]);
    }

    public function getAllServices(Request $request)
    {
        try {
            $services = Service::all();
            return successResponse('Services fetched successfully', ['services' => $services], $request);
        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function createSelfServiceOrder(Request $request)
    {
        try {
            $this->admin = $request->user();
            $validator = Validator::make($request->all(), [
                'customer_id' => 'bail|required|exists:users,id,deleted_at,NULL',
                'services' => 'bail|required|array',
                'services.*.id' => 'bail|required|distinct|exists:services,id',
                'services.*.quantity' => 'bail|required|integer|min:1',
                'payment_method' => 'bail|required|exists:payment_methods,name'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $location = Session::get('authLocation');
            $request->merge(['location' => $location->id, 'company' => $location->company_id, 'user_id' => $request->customer_id]);
            $orderHandler = new OrderRequestHandler($request->toArray(), $this->admin);
            $result = $orderHandler->createSelfServiceOrder();
            if ($result['status']){
                $responseMessage = $result['message'];
                $order = $orderHandler->getOrder();
                $responseData = [
                    'order' => new OrderResource($order)
                ];
                return successResponse($responseMessage, $responseData, $request);
            }
            throw new \Exception($result['message']);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function createDropOffOrder(Request $request)
    {
        try {
            $this->admin = $request->user();
            $validator = Validator::make($request->all(), [
                'customer_id' => 'bail|required|exists:users,id,deleted_at,NULL',
                'note' => 'bail|nullable|max:255',
                'bags' => 'bail|required|integer|min:1|max:50',
                'payment_method' => 'bail|nullable|exists:payment_methods,name',
                'pickup_order_id' => 'nullable|exists:order_requests,kwik_order_id'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $location = Session::get('authLocation');

            $customer = User::find($request->customer_id);
            if(!empty($request->get('pickup_order_id'))) {
                $isValidOrderRequest = $this->validateKwikOrderIdForDropoff($request->pickup_order_id, $request->customer_id);
                if(!$isValidOrderRequest->status){
                    return errorResponse($isValidOrderRequest->message, 400, $request);
                }
            } elseif ($customer->hasUnmatchedPickup($location->id)){
                throw ValidationException::withMessages([
                    'pickup_order_id' => "This customer has an un-matched pickup order. Please specify the 'PICKUP ORDER ID' to proceed"
                ]);
            }

            $paymentMethod = ($request->get('payment_method')) ? PaymentMethod::where('name', $request->payment_method)->first()->id: null;

            $order = Order::create([
                'user_id' => $customer->id,
                'order_type' => Meta::DROP_OFF_ORDER_TYPE,
                'status' => Meta::ORDER_STATUS_PENDING,
                'amount' => 0,
                'payment_method' => $paymentMethod,
                'note' => $request->note,
                'bags' => $request->bags,
                'created_by' => $this->admin->id,
                'location_id' => $location->id,
                'company_id' => $location->company_id,
                'collected' => 0
            ]);
            if(!empty($request->get('pickup_order_id'))){
                $orderRequest = OrderRequest::where('kwik_order_id', $request->pickup_order_id)->first();
                $orderRequest->update([
                    'order_id' => $order->id
                ]);
                $order->update([
                    'pickup_cost' => $orderRequest->amount
                ]);
            }

            $request->merge(['location' => $location->id, 'user_id' => $request->customer_id]);

            (new OrderRequestHandler($request->all(), $this->admin, $order))->createUserDiscountOrFail();

            Mail::to($order->user)->send(new DropoffOrderCreated($order));

            $responseMessage = 'Order created successfully';
            Session::put('ORDER_IN_SESSION', $order);
            $responseData = [
                'order' => new OrderResource($order)
            ];
            return successResponse($responseMessage, $responseData, $request);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function updateOrder(Request $request)
    {
        try {
            $this->admin = $request->user();
            $location = Session::get('authLocation');
            $order = ($request->order_id)? Order::where('location_id', $location->id)->where('id', $request->order_id)->first(): null;
            if(empty($order)){
                return errorResponse('The selected order id is invalid', 400, $request);
            }
            $validator = Validator::make($request->all(), [
                'services' => 'required|array',
                'services.*.id' => 'required|distinct|exists:services,id',
                'services.*.quantity' => 'required|integer|min:1',
                'lockers' => [
                    'bail',
                    'nullable',
                    Rule::requiredIf(function() use($order){
                        return ($order->order_type === Meta::DROP_OFF_ORDER_TYPE);
                    }),
                    'array'
                ],
                'lockers.*' => [
                    'bail',
                    function ($attribute, $value, $fail) use ($order) {
                        if($value) {
                            //locker is 0 when out of locker is selected (multiple orders can be out of locker)
                            if ($value !== 0) {
                                $occupiedLockers = OrdersLocker::whereHas('locker', function ($q) use ($value, $order) {
                                    $q->where('location_id', $order->location->id)
                                        ->where('occupied', 1)
                                        ->where('locker_number', $value);
                                })->where('order_id', '<>', $order->id);
                                if ($occupiedLockers->count() > 0) {
                                    $fail('Locker ' . $value . ' is already occupied');
                                }
                            }
                        }
                    }
                ],
                'payment_method' => [
                    'bail',
                    'nullable',
                    Rule::requiredIf(function() use ($order) {
                        return ($order && $order->order_type === Meta::SELF_SERVICE_ORDER_TYPE);
                    }),
                    'exists:payment_methods,name'
                ]
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            if($order->status === Meta::ORDER_STATUS_COMPLETED){
                return errorResponse('Cannot update a completed order', 400, $request);
            }

            if($order->order_type === Meta::SELF_SERVICE_ORDER_TYPE){

                if(is_null($request->payment_method)){
                    return errorResponse('Payment method selected is invalid', 400, $request);
                }
            }

            $authUser = $this->admin;
            $orderHandler = new OrderRequestHandler($request->toArray(), $authUser, $order);
            $result = $orderHandler->updateOrder();
            if ($result['status']){
                $responseMessage = $result['message'];
                $order = Order::find($orderHandler->getOrder()->id);
                $responseData = [
                    'order' => new OrderResource($order)
                ];
                return successResponse($responseMessage, $responseData, $request);
            }else{
                throw new \Exception($result['message']);
            }

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function getLocationLockers(Request $request)
    {
        try {
            $location = Session::get('authLocation');
            $lockers = $location->lockers;
            $responseData = [
                'lockers' => LockerResource::collection($lockers)->toArray($request)
            ];
            return successResponse('Success', $responseData, $request);
        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function markOrderAsCollected(Request $request)
    {
        try {
            $location = Session::get('authLocation');
            $validator = Validator::make($request->all(), [
                'order_id' => ['bail','required', Rule::exists('orders', 'id')
                    ->whereNull('deleted_at')->where('location_id', $location->id)]
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $order = Order::find($request->order_id);
            if($order->status === Meta::ORDER_STATUS_PENDING){
                return errorResponse('Order still pending and cannot be marked as collected', 400, $request);
            }
            if($order->lockers()->count() === 0){
                return errorResponse('No locker associated with this order', 400, $request);
            }
            $order->markAsCollected();
            return successResponse('Order marked as collected', [
                'order' => new OrderResource($order)
            ]);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    function validateKwikOrderIdForDropoff($kwikPickupOrderID, $userID)
    {
        $result = (object) [
            'status' => false,
            'message' => 'Invalid Pickup Order Selected'
        ];
        $location = Session::get('authLocation');
        $orderRequest = OrderRequest::where('kwik_order_id', $kwikPickupOrderID)->first();
        if($orderRequest->user_id != $userID){
            $result->message = 'The pickup order does not belong to the specified customer';
        } elseif ($orderRequest->order_id){
            $result->message = 'The specified pickup order id is already associated with an order';
        } elseif ($orderRequest->location_id != $location->id){
            $result->message = "The specified pickup order is set to be dropped-off in a different location '{$orderRequest->store_location->name}'";
        } else {
            $result->status = true;
            $result->message = 'Valid';
        }
        return $result;
    }

}
