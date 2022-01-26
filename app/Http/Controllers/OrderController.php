<?php

namespace App\Http\Controllers;

use App\Classes\Meta;
use App\Classes\OrderRequestHandler;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Company;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrdersLocker;
use App\Models\OrderType;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\User;
use App\Notifications\CollectedOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create_order', ['only' => ['create', 'save']]);
        $this->middleware('permission:edit_order', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_order,delete_order_permanently', ['only' => ['delete']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $authUser = $this->getAuthUser();
        $orders = $this->getFilteredOrders($request, 20);
        $paymentMethods = PaymentMethod::all();
        $orderTypes = OrderType::all();
        $locations = Location::getAllowed();
        $companies = (!$authUser->company_id) ? Company::all() : null;
        return view('order.list', compact('orders', 'authUser', 'paymentMethods', 'orderTypes', 'locations', 'companies'));
    }

    /**
     * Show the form for creating a new order.
     *
     * @param User $customer
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(User $customer)
    {
        $authUser = $this->getAuthUser();
        $customer = User::where('id', $customer->id)->with(['location'])->first();
        $paymentMethods = PaymentMethod::all();
        $services = Service::all();
        $locations = ($authUser->can('list_locations')) ? Location::getAllowed() : null;
        return view('order.create', compact('services', 'paymentMethods', 'customer', 'authUser', 'locations'));
    }

    /**
     * Save the newly created order
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(Request $request)
    {
        $authUser = $this->getAuthUser();
        if($authUser->location_id){
            $request['location'] = $authUser->location_id;
        }
        //Add validation for required services and payment_method
        $this->validate($request, [
            'location' => 'required|exists:locations,id,deleted_at,NULL',
            'user_id' => 'required|exists:users,id',
            'services' => 'required|array',
            'services.*.id' => 'required|distinct|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|exists:payment_methods,name'
        ]);
        try {
            $request['company'] = Location::find($request->location)->company_id;
            $orderHandler = new OrderRequestHandler($request->toArray(), $authUser);
            $result = $orderHandler->createSelfServiceOrder();
            if ($result['status']){
                $responseMessage = $result['message'];
                $order = $orderHandler->getOrder();
                return redirect()
                    ->route('customer.view', ['customer' => $order->user_id])
                    ->with(['status' => 'success', 'title' => 'OK', 'message' => $responseMessage]);

            }else{
                throw new \Exception($result['message']);
            }
        } catch (\Exception $e){
            return redirect()->back()
                ->with(['status' => 'success', 'title' => 'OK', 'message' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified order.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view(Request $request, Order $order)
    {
        $authUser = $this->getAuthUser();
        if($order->status ===  Meta::ORDER_STATUS_PENDING ){
            $paymentMethods = PaymentMethod::all();
            $services = Service::all();
            if($authUser->can('list_locations')){
                $locations = Location::getAllowed();
            }
            $lockers = null;
            if($order->order_type === Meta::DROP_OFF_ORDER_TYPE){
                $lockers = $order->location->lockers()->where('locker_number', '<>', 0)->get();
            }
            return view('order.edit', compact('order','services', 'paymentMethods', 'authUser', 'locations', 'lockers'));
        }
        $lockers = !empty($order->locker_numbers) ? json_decode($order->locker_numbers, true) : null;
        return view('order.view', compact('order',   'authUser', 'lockers'));
    }

    /**
     * Update the specified order.
     *
     * @param UpdateOrderRequest $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        if($order->status === Meta::ORDER_STATUS_COMPLETED){
            return redirect()->route('order.list')
                ->with([
                    'title' => 'Not Allowed',
                    'status' => 'error',
                    'message' => 'Cannot update a completed order'
                ]);
        }
        try {
            $authUser = $this->getAuthUser();
            $orderHandler = new OrderRequestHandler($request->toArray(), $authUser, $order);

            $result = $orderHandler->updateOrder();
            if ($result['status']){
                $responseMessage = $result['message'];
                $order = $orderHandler->getOrder();
                return redirect()
                    ->route('customer.view', ['customer' => $order->user_id])
                    ->with(['status' => 'success', 'title' => 'OK', 'message' => $responseMessage]);

            }else{
                throw new \Exception($result['message']);
            }
        } catch (\Exception $e){
            Log::error($e->getTraceAsString());
            return redirect()->back()
                ->with(['status' => 'success', 'title' => 'OK', 'message' => 'Something went wrong: contact admin'])
                ->withInput();
        }
    }

    /**
     * Delete the specified order
     *
     * @param Order $order
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Order $order, Request $request)
    {
        $authUser = $this->getAuthUser();
        $order = Order::allowed($authUser)->where('id', $order->id)->first();
        if(empty($order)){
            return redirect()->route('home')->with([
                'status' => 'error',
                'title' => 'Not found',
                'order' => 'You do not have access to this order: contact admin if required'
            ]);
        }
        if($order->status === Meta::ORDER_STATUS_COMPLETED){
            return redirect()->route('home')->with([
                'status' => 'error',
                'title' => 'Action rejected',
                'order' => 'Order already complete and cannot be deleted'
            ]);
        }
        if($authUser->can('delete_order_permanently')){
            $this->validate($request, [
                'deletion_type' => array(
                    'required',
                    Rule::in(['temporary', 'permanent'])
                )
            ]);
            DB::beginTransaction();
            try {
                if ($request->deletion_type === 'permanent') {
                    $order->forceDelete();
                } else {
                    $order->lockers()->delete();
                    $order->delete();
                }
                DB::commit();
                return redirect(route('order.list'))->with(['status' => 'success', 'message' => 'Order deleted successfully', 'title' => 'OK']);
            } catch(\Exception $e){
                DB::rollback();
                return redirect()->back()->withErrors(['deletion_failed' => 'An error occurred: Unable to delete order'])
                    ->withInput();
            }
        }
        $order->delete();
        return redirect(route('order.list'))->with(['status' => 'success','title' => 'OK', 'message' => "Order deleted successfully"]);
    }

    public function flagOrderAsCollected(Request $request, Order $order)
    {
        try{

            $order->markAsCollected();

            return back()->with(['status' => 'success','title' => 'OK', 'message' => "This order has been collected by the customer."]);
        }catch(\Exception $e){
            return back()->with(['status' => 'error','title' => 'Oops', 'message' => "Something went wrong, please try again later."]);
        }

    }
}
