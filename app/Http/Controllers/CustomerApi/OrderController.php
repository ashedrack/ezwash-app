<?php

namespace App\Http\Controllers\CustomerApi;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{

    protected $guard = 'users';
    protected $user;

    public function __construct(){

        $this->user = $this->getAuthUser($this->guard);
    }
    /**
     * Display all orders.
     * 
     */
    public function viewAllOrders(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'records_per_page' => 'bail|nullable|integer|max:50',
                'page' => 'bail|nullable|integer|min:1'
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $recordsPerPage = (!empty($request->get('records_per_page'))) ?
                $request->records_per_page : 20;
            $orders = new OrderCollection($this->user->orders()->orderBy('created_at', 'DESC')->paginate($recordsPerPage));

            $responseData = $orders->toArray($request);
            $responseMessage = "Found {$orders->total()} orders";

            if($orders->total() === 0){
                $responseMessage = "No orders found";
            }
            return successResponse($responseMessage, $responseData, $request);

        }catch(\Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }
        
    }
    /**
     * Display the specified order.
     * 
     * @param Request $request
     * @return 
     */
    public function viewOrder(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'order_id' => ['bail', 'required', 'exists :orders,id', function($attribute, $value, $fail){
                    if(!User::find($this->user->id)->orders()->where('id', $value)->exists()){
                        $fail("Invalid $attribute selected");
                    }
                }]
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $order = Order::find($request->order_id);
            
            $responseData = ['order' => new OrderResource($order)];
            return successResponse('Success', $responseData);
        }catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        }catch(\Exception $e){
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

}
