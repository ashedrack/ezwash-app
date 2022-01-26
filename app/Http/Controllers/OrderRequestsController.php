<?php

namespace App\Http\Controllers;

use App\Classes\Meta;
use App\Models\Company;
use App\Models\Location;
use App\Models\OrderRequest;
use App\Models\OrderRequestStatus;
use App\Models\OrderRequestType;

use Illuminate\Http\Request;

class OrderRequestsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:edit_order', ['only' => ['cancel']]);
    }
    public function index(Request $request)
    {
        $authUser = $this->getAuthUser();
        $orderRequests = $this->getFilteredOrderRequests($request, 20);
        $orderTypes = OrderRequestType::all();
        $orderRequestStatuses = OrderRequestStatus::all();
        $locations = (!$authUser->location_id) ? Location::allowedToAccess($authUser)->get() : null;
        $companies = (!$authUser->company_id) ? Company::all() : null;
        return view('orderRequest.index', compact('orderRequests', 'authUser', 'orderTypes', 'orderRequestStatuses', 'locations', 'companies'));
    }
    public function view(Request $request, OrderRequest $order_request)
    {
        $authUser = $this->getAuthUser();
        $order = $order_request->order;
        return view('orderRequest.view', compact('order', 'authUser', 'order_request'));
    }
    public function cancel(Request $request, OrderRequest $order_request)
    {
        $request_type = ucwords($order_request->order_request_type->name);
        $order_request->update([
            'order_request_status_id' => $order_request->order_request_type_id === Meta::DELIVERY_ORDER_REQUEST_TYPE ? Meta::DELIVERY_CANCELED_STATUS : Meta::PICKUP_CANCELED_STATUS
        ]);
        return redirect()->back()->with(['status' => 'success', 'title' => 'OK', 'message' => "{$request_type} order request has been canceled successfully."]);

    }

}
