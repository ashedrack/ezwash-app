<?php

namespace App\Http\Controllers;

use App\Classes\Meta;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrdersStatus;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param null $guard
     * @return Authenticatable|Employee|null
     */
    public function getAuthUser($guard = null){
        return auth($guard)->user();
    }

    /**
     * @param Request $request
     * @param Authenticatable|Employee $authUser
     * @return Employee|\Illuminate\Database\Eloquent\Builder
     */
    public function getFilteredEmployees(Request $request, $authUser, $records_per_page, $company_id = null)
    {
        $companyID = $authUser->company_id ?? $request->employee_company ?? null;
        $locationID = $authUser->location_id ?? $request->employee_location ?? null;
        $roleID = $request->employee_role;

        $employeesQuery = Employee::with('location', 'company');
        if($authUser->_isOverallAdmin()){
            if($company_id){
                $employeesQuery = $employeesQuery->where('company_id', $company_id);
            }
        }else{
            $employeesQuery = $employeesQuery->where('company_id', $companyID);
        }

        if($locationID){
            $employeesQuery = $employeesQuery->where('location_id', $locationID);
        }
        if($roleID){
            $employeesQuery = $employeesQuery->whereHas('roles', function ($q) use ($roleID){
                $q->where('id' , $roleID);
            });
        }
        if($request->get('employee_name_or_email')){
            $employeesQuery = $employeesQuery->where(function($q) use ($request) {
                $q->where('name', 'like', "%$request->employee_name_or_email%")
                    ->orWhere('email', 'like', "%$request->employee_name_or_email%");
            });
        }

        session(['employeeFilterParams' => $request->toArray()]);
        return $employeesQuery->orderBy('created_at', 'desc')
            ->paginate($records_per_page, ['*'],'employee_page');
    }

    public function getFilteredUsers(Request $request){
        $users = User::with('location');
        if($request->get('location')){
            $users = $users->where('location_id', $request->location);
        }
        if($request->get('search_string')){
            $users = $users->where('name', 'LIKE', "%$request->search_string%")
                ->orWhere('email', 'LIKE', "%$request->search_string%")
                ->orWhere('phone', 'LIKE', "%$request->search_string%");
        }
        return $users;
    }

    public function siteWideUserFilter($query_string, $records_per_page = 20, $page = 1){
        $users = User::where('name', 'LIKE', "%$query_string%")
            ->orWhere('email', 'LIKE', "%$query_string%")
            ->paginate($records_per_page, ['*'],'page', $page);
        return $users;
    }

    /**
     * @param Request|array $requestOrParams
     * @param integer $records_per_page
     * @param integer $company_id
     * @return \Illuminate\Pagination\Paginator
     */
    public function getFilteredOrders($requestOrParams, $records_per_page = 20, $company_id = null){
        if(!$requestOrParams instanceof Request){
            $request = $requestOrParams['request'];
            $records_per_page = (array_key_exists('records_per_page',$requestOrParams)) ? $requestOrParams['records_per_page']: 20;
            $company_id = (array_key_exists('company_id',$requestOrParams))? $requestOrParams['company_id']: null;
            $location_id = (array_key_exists('location_id',$requestOrParams))? $requestOrParams['location_id']: null;
            $user_id = (array_key_exists('user_id',$requestOrParams))? $requestOrParams['user_id']: null;
        }else{
            $request = $requestOrParams;
            $location_id = $request->get('order_location');
            $company_id = $company_id ?? $request->get('order_company');
        }

        /**
         * @var Builder $ordersQuery
         */
        $ordersQuery = Order::with(['user', 'location', 'orderType'])->allowed($this->getAuthUser());
        if(!empty($user_id)){
            $ordersQuery->where('user_id', $user_id);
        }
        if($company_id){
            $ordersQuery = $ordersQuery->where('company_id', $company_id);
        }
        if($location_id){
            $ordersQuery = $ordersQuery->where('location_id', $location_id);
        }
        if($request->get('order_type')){
            $ordersQuery = $ordersQuery->where('order_type', $request->order_type);
        }
        if($request->get('payment_method')){
            $ordersQuery = $ordersQuery->where('payment_method', $request->payment_method);
        }
        if($request->get('order_id')){
            $ordersQuery = $ordersQuery->where('id', $request->order_id);
        }
        if($request->get('user_name_or_email')){
            $ordersQuery = $ordersQuery->whereHas('user', function (Builder $q) use ($request){
                $q->whereRaw(searchQueryConstructor($request->user_name_or_email, ['name', 'email']));
            });
        }

        $coalesceDateString = Order::COALESCE_DATE_STRING;
        if($request->get('filter_start_date')){
            $ordersQuery = $ordersQuery->whereRaw("DATE($coalesceDateString) >= ?", [$request->filter_start_date]);
        }
        if($request->get('filter_end_date')){
            $ordersQuery = $ordersQuery->whereRaw("DATE($coalesceDateString) <= ?", [$request->filter_end_date]);
        }
        $completedOrderQuery = (clone $ordersQuery)->where('status', OrdersStatus::COMPLETED);
        $pendingOrderQuery = (clone $ordersQuery)->where('status', OrdersStatus::PENDING);

        $stats = [
            'completed_orders_amount' => $completedOrderQuery->sum('amount'),
            'completed_orders_count' => $completedOrderQuery->count('id'),
            'pending_orders_amount' => $pendingOrderQuery->sum('amount'),
            'pending_orders_count' => $pendingOrderQuery->count('id')
        ];

        if($request->get('order_status')) {
            if ($request->order_status == 'pending') {
                $ordersQuery = $ordersQuery->where('status', Meta::ORDER_STATUS_PENDING)
                    ->where('amount', 0);
            } elseif ($request->order_status == 'awaiting_payment') {
                $ordersQuery = $ordersQuery->where('status', Meta::ORDER_STATUS_PENDING)
                    ->where('amount', '>', 0);
            } else {
                $ordersQuery = $ordersQuery->where('status', Meta::ORDER_STATUS_COMPLETED);
            }
        }
        session(['orderFilterParams' => $request->toArray(), 'filteredOrdersAggregate' => (object)$stats]);
        return $ordersQuery->orderByRaw("$coalesceDateString DESC")
            ->paginate($records_per_page, ['*'],'order_page');
    }

    public function getFilteredOrderRequests($requestOrParams, $records_per_page = 20){
        $authUser = $this->getAuthUser();
        if(!$requestOrParams instanceof Request){
            $request = $requestOrParams['request'];
            $records_per_page = (array_key_exists('records_per_page',$requestOrParams)) ? $requestOrParams['records_per_page']: 20;
        }else{
            $request = $requestOrParams;
            $location_id = $authUser->location_id ?? $request->get('order_location');
            $company_id = $authUser->company_id ?? $request->get('order_company');
        }
        $orderRequestsQuery = OrderRequest::leftJoin('locations', 'locations.id', 'order_requests.location_id');

        if($company_id){
            $orderRequestsQuery = $orderRequestsQuery->where('locations.company_id', $company_id);
        }
        if($location_id){
            $orderRequestsQuery = $orderRequestsQuery->where('locations.id', $location_id);
        }
        if($request->get('order_request_type')){
            $orderRequestsQuery = $orderRequestsQuery->where('order_request_type_id', $request->order_request_type);
        }

        if($request->get('order_request_status')) {
            $orderRequestsQuery = $orderRequestsQuery->whereIn('order_request_status_id', $request->order_request_status);
        }
        if($request->get('filter_start_date')){
            $orderRequestsQuery = $orderRequestsQuery->whereDate('order_requests.created_at', '>=', $request->filter_start_date);
        }
        if($request->get('filter_end_date')){
            $orderRequestsQuery = $orderRequestsQuery->whereDate('order_requests.created_at', '<=', $request->filter_end_date);
        }
        if($request->get('kwik_order_id')){
            $orderRequestsQuery = $orderRequestsQuery->whereRaw(
                searchQueryConstructor($request->kwik_order_id, ['order_requests.kwik_order_id'])
            );
        }
        if($request->get('user_name_or_email')){
            $orderRequestsQuery = $orderRequestsQuery->whereHas('user', function ($q) use ($request){
                $q->whereRaw(searchQueryConstructor($request->user_name_or_email, ['name', 'email']));
            });
        }
        session(['orderFilterParams' => $request->toArray()]);
        return $orderRequestsQuery->orderBy('order_requests.created_at', 'desc')
            ->paginate($records_per_page, ['order_requests.*'],'order_page');
    }

    public function getFilteredTransactions($requestOrParams, $records_per_page = 20){
        if(!$requestOrParams instanceof Request){
            $request = $requestOrParams['request'];
            $records_per_page = (array_key_exists('records_per_page',$requestOrParams)) ? $requestOrParams['records_per_page']: 20;
        }else{
            $request = $requestOrParams;
            $location_id = $request->get('order_location');
        }
        $transactions = Transaction::allowed($request->user());

        if($request->get('payment_method')){
            $transactions = $transactions->where('transaction_payment_method_id', $request->payment_method);
        }

        if($request->get('transaction_status')) {
            $transactions = $transactions->where('transaction_status_id', $request->transaction_status);
        }
        if($request->get('user_name_or_email')){
            $transactions = $transactions->whereHas('user', function ($q) use ($request){
                $q->whereRaw(searchQueryConstructor($request->user_name_or_email, ['name', 'email']));
            });
        }
        if($request->get('transaction_reference')){
            $transactions = $transactions->where('reference_code', 'LIKE', "%{$request->transaction_reference}%");
        }
        session(['orderFilterParams' => $request->toArray()]);
        return $transactions->orderBy('updated_at', 'desc')
            ->paginate($records_per_page, ['*'],'order_page');
    }
    public function encrypt_decrypt($action, $string)
    {
        $output = false;

        $encrypt_method = config('app.ENCRYPT_METHOD');
        $secret_key = config('app.ENCRYPTION_SECRET_KEY');
        $secret_iv = config('app.ENCRYPTION_SECRET_IV');
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

}
