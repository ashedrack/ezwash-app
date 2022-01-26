<?php

namespace App\Http\Controllers\AdminApi;

use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderRequestType;
use App\Models\User;
use App\Rules\isValidEmailOrPhone;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    protected $guard = 'admins';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $authLocation;
    public function __construct()
    {
        $this->middleware('permission:create_customer', ['only' => ['createCustomer']]);
        $this->middleware('permission:list_customers', ['only' => ['searchCustomer']]);
        $this->middleware('permission:single_customer_search', ['only' => ['findSingleCustomer']]);
        $this->middleware('permission:list_orders', ['only' => ['getCustomerOrders']]);
        $this->middleware('permission:view_order', ['only' => ['getCustomerOrder']]);
    }

    public function createCustomer(Request $request){
        try{
            $admin = $request->user();
            $validator = Validator::make($request->all(), [
                'name' => 'bail|required|max:200',
                'email' => 'bail|required|email|unique:users,email|max:200',
                'phone' => ['bail','required', new ValidPhone,
                    function ($attribute, $value, $fail) {
                        $phone = cleanUpPhone($value);
                        if(User::where('phone', $phone)->count() > 0){
                            $fail("User with specified phone number already exists");
                        }
                    }
                ],
                'gender' => ['bail','required', Rule::in(['male', 'female'])]
            ]);

            if($validator->fails()){
                throw new ValidationException($validator);
            }
            $authLocation = Session::get('authLocation');
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => cleanUpPhone($request->phone),
                'gender' => $request->gender,
                'location_id' => $authLocation->id,
                'location_on_create' => $authLocation->id,
                'created_by' => $admin->id
            ]);
            $responseData = [
                'customer' => new UserResource($user)
            ];
            return successResponse('Customer created successful', $responseData, $request);

        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = implode(' ', $errors);
            return errorResponse($message, 400, $request);

        } catch (\Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function searchCustomer(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'query_string' => 'bail|required|max:200',
                'records_per_page' => 'bail|nullable|integer|max:50',
                'page' => 'bail|nullable|integer|min:1'
            ]);

            if($validator->fails()){
                throw new ValidationException($validator);
            }

            $recordsPerPage = (!empty($request->get('records_per_page'))) ?
                $request->records_per_page : 20;

            $usersFound = User::where('name', 'LIKE', "%{$request->query_string}%")
                    ->orWhere('email', 'LIKE', "%{$request->query_string}%")
                    ->paginate($recordsPerPage);

            $users = new UserCollection($usersFound);
            $responseData = $users->toArray($request);
            unset($responseData['links']);

            $responseMessage = "Found {$users->total()} customers matching your search";
            if($users->total() == 0) $responseMessage = 'No results found';

            return successResponse($responseMessage, $responseData, $request);

        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function findSingleCustomer(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'query_string' => ['bail','required','max:200',new isValidEmailOrPhone],
            ]);

            if($validator->fails()){
                throw new ValidationException($validator);
            }
            $phone = cleanUpPhone($request->query_string);
            $userFound = User::where('email', $request->query_string)
                ->orWhere('phone', $phone)->first();
            if(empty($userFound)){
                return errorResponse('User not found', 400, $request);
            }
            $user = new UserResource($userFound);
            $responseData = [
                'customer' => $user
            ];
            $responseMessage = "User exists";

            return successResponse($responseMessage, $responseData, $request);

        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function findCustomerWithPickupOrderId(Request $request)
    {
        $errorRes = (object) [
            'status' => false,
            'message' => "Something went wrong",
            'code' => 400,
            'trace' => null
        ];
        try{
            $validator = Validator::make($request->all(), [
                'pickup_order_id' => ['bail','required','max:200', 'exists:order_requests,kwik_order_id'],
            ]);

            if($validator->fails()){
                throw new ValidationException($validator);
            }
            $orderRequest = OrderRequest::where('kwik_order_id', $request->pickup_order_id)->first();

            if($orderRequest->order_request_type_id !== OrderRequestType::PICKUP){
                $errorRes->message = 'The specified pickup order id is a "delivery" request, "pickup" request id expected';
            } elseif (!empty($orderRequest->order_id)) {
                $errorRes->message = 'The specified pickup order id is already associated with a dropoff order "#' . $orderRequest->order_id . '"';
            } else {
                $userFound = $orderRequest->user;
                if (!empty($userFound)) {
                    $user = new UserResource($userFound);
                    $responseData = [
                        'customer' => $user
                    ];
                    $responseMessage = "Successful";
                    return successResponse($responseMessage, $responseData, $request);
                } else{
                    $errorRes->message = 'User not found';
                }
            }

        }catch (ValidationException $e){
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $errorRes->message = $errors[0];

        } catch (\Exception $e){
            $errorRes->code = 500;
            $errorRes->trace = $e;
        }
        return errorResponse($errorRes->message, $errorRes->code, $request, $errorRes->trace);
    }

    public function getCustomerOrders(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'bail|required|exists:users,id,deleted_at,NULL',
                'records_per_page' => 'bail|nullable|integer|max:50',
                'page' => 'bail|nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $authLocation = Session::get('authLocation');
            $user = User::find($request->customer_id);
            $recordsPerPage = (!empty($request->get('records_per_page'))) ?
                $request->records_per_page : 20;
            $orders = new OrderCollection($user->orders()->where('location_id', $authLocation->id)->paginate($recordsPerPage));

            $responseData = $orders->toArray($request);

            $responseMessage = "Found {$orders->total()} orders";
            if($orders->total() === 0){
                $responseMessage = "No orders found";
            }
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

    public function getCustomerOrder(Request $request){
        try {
            $location = Session::get('authLocation');
            $validator = Validator::make($request->all(), [
                'customer_id' => 'bail|required|exists:users,id,deleted_at,NULL',
                'order_id' => [
                    'bail',
                    'required',
                    Rule::exists('orders', 'id')->where(function($query) use($location) {
                        $query->whereNull('deleted_at')
                            ->where('location_id', $location->id)
                            ->where('user_id', request('customer_id'));
                    })
                ]
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $orderResult = Order::where('user_id', $request->customer_id)->where('location_id', $location->id)->find($request->order_id);
            $responseData = [
                'order' => new OrderResource($orderResult)
            ];
            return successResponse('Order details', $responseData, $request);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }
}
