<?php

namespace App\Classes;

use App\Models\Company;
use App\Models\Employee;
use App\Models\KwikPickupsAndDelivery;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderRequestStatus;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class StatisticsFilters
 *
 * @property Request $request
 * @property Authenticatable|Employee $authUser
 * @property int|null $company
 * @property int|null $location
 * @property string|null $startDate
 * @property string|null $endDate
 *
 * @package App\Classes
 */
class StatisticsFilters {

    protected $request;
    protected $authUser;
    protected $company;
    protected $location;
    protected $startDate;
    protected $endDate;

    /**
     * StatisticsFilters constructor.
     *
     * @param Request|null $request
     * @param Authenticatable|Employee $authUser
     */
    public function __construct(Request $request = null,  $authUser = null) {
        $this->request = $request;
        $this->authUser = $authUser;
        $this->company = ($authUser && $authUser->company_id) ? $authUser->company_id : $request->company;
        $this->location = ($authUser &&  $authUser->location_id) ? $authUser->location_id : $request->location;
        $this->startDate = $request->start_date;
        $this->endDate = $request->end_date;
    }

    public static $defaultStats = [
        'cash_income' => 0,
        'pos_income' => 0,
        'card_income' => 0,
        'total_income' => 0,
        'all_time_income' => 0,
        'completed_orders' => 0,
        'pending_orders' => 0,
        'pending_income' => 0,
        'pickup_requests' => 0,
        'delivery_requests' => 0,
        'orders' => [],
        'transactions' => []
    ];

    /**
     * @param array $filterOptions
     * @return object
     */
    public function generalStatistics($filterOptions)
    {
        $user = $filterOptions['user'] ?? null;
        $showOrders = $filterOptions['showOrders'] ?? false;
        $showTransactions = $filterOptions['showTransactions'] ?? false;
        $month_counts = 13;
        $month_label_lists = [];

        $resultObject = (object)self::$defaultStats;
        $discountsQuery = User::leftJoin('users_discounts', function($join){
            $join->on('users.id', 'users_discounts.user_id');
        })->where('users_discounts.status', Meta::USED_DISCOUNT)->orderBy('created_at', "DESC");
        $newCustomerQuery = User::whereNull('deleted_at')->whereDate('created_at', now()->toDateString());
        $orderBaseQuery = Order::setEagerLoads([]);
        $baseQuery = clone $orderBaseQuery;

        $baseTransactionsQuery = Transaction::leftJoin('orders', 'orders.id', 'transactions.order_id')->where('transaction_type_id', TransactionType::ORDER_PAYMENT_ID);
        $extraTransactionQueryForStats = Transaction::where('transaction_type_id', TransactionType::ORDER_PAYMENT_ID);
        for($e=1; $e < $month_counts; $e++)
        {
            $month = sprintf("%02s", $e);
            $month_label_lists[] = (clone $baseQuery)->where('status', ORDER_STATUS_COMPLETED)->whereYear('created_at', now()->year)->whereRaw("MONTH(updated_at) = ? ", [$month])->sum(DB::raw("amount + pickup_cost + delivery_cost"));
        }
        if ($this->company) {
            $baseQuery = $baseQuery->where('company_id', $this->company);
            $baseTransactionsQuery = $baseTransactionsQuery->where('orders.company_id', $this->company);
        }
        if ($this->location) {
            $baseQuery = $baseQuery->where('location_id', $this->location);
            $baseTransactionsQuery = $baseTransactionsQuery->where('orders.location_id', $this->location);
            $discountsQuery = $discountsQuery->where('users.location_id', $this->location);
        }
        if ($user) {
            $baseQuery = $baseQuery->where('user_id', $user);
            $baseTransactionsQuery = $baseTransactionsQuery->where('orders.user_id', $user);
        }
        if ($this->startDate) {
            $discountsQuery = $discountsQuery->whereDate('users_discounts.updated_at', '>=', $this->startDate);
            $baseQuery = $baseQuery->whereDate('created_at', '>=', $this->startDate);
            $baseTransactionsQuery = $baseTransactionsQuery->whereDate('transactions.created_at', '>=', $this->startDate);
            $newCustomerQuery = $newCustomerQuery->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $discountsQuery = $discountsQuery->whereDate('users_discounts.updated_at', '<=', $this->endDate);
            $baseQuery = $baseQuery->whereDate('updated_at', '<=', $this->endDate);
            $baseTransactionsQuery = $baseTransactionsQuery->whereDate('transactions.updated_at', '<=', $this->endDate);
            $newCustomerQuery = $newCustomerQuery->whereDate('created_at', '<=', $this->endDate);
        }

        $orderRequestQuery = OrderRequest::from('order_requests as O_REQ')
            ->leftJoin('orders', function (JoinClause $join) {
                $join->on('orders.id', '=', 'O_REQ.order_id')
                    ->where('orders.status', Meta::ORDER_STATUS_COMPLETED);
                if($this->company){
                    $join = $join->where('orders.company_id', $this->company);
                }
                if($this->location){
                    $join = $join->where('orders.location_id', $this->location);
                }
                if($this->startDate){
                    $join = $join->whereDate('orders.completed_at', '>=', $this->startDate);
                }
                if($this->endDate){
                    $join = $join->whereDate('orders.completed_at', '<=', $this->endDate);
                }
            })
            ->whereNotNull('orders.id')
            ->where('orders.status', Meta::ORDER_STATUS_COMPLETED);


        $resultObject->all_time_income = $orderBaseQuery->where('status', ORDER_STATUS_COMPLETED)->sum("amount");
        $revenueQuery = (clone $baseQuery)->where('status', ORDER_STATUS_COMPLETED);

        $orderBaseQueryByPaymentMethod = (clone $revenueQuery)
            ->select([
                'payment_method',
                DB::raw('SUM(amount) as income'),
                DB::raw('COUNT(orders.id) as count'),
                DB::raw('SUM(pickup_cost + delivery_cost) as pickup_delivery_income'),
            ])->groupBy('payment_method')->get();

        $resultObject->card_income = $orderBaseQueryByPaymentMethod->where('payment_method', CARD_PAYMENT)->first()->income ?? 0;
        $resultObject->card_income_count = $orderBaseQueryByPaymentMethod->where('payment_method', CARD_PAYMENT)->first()->count ?? 0;

        $resultObject->cash_income = $orderBaseQueryByPaymentMethod->where('payment_method', CASH_PAYMENT)->first()->income ?? 0;
        $resultObject->cash_income_count = $orderBaseQueryByPaymentMethod->where('payment_method', CASH_PAYMENT)->first()->count ?? 0;

        $resultObject->pos_income = $orderBaseQueryByPaymentMethod->where('payment_method', POS_PAYMENT)->first()->income ?? 0;
        $resultObject->pos_income_count = $orderBaseQueryByPaymentMethod->where('payment_method', POS_PAYMENT)->first()->count ?? 0;

        $resultObject->total_income = $orderBaseQueryByPaymentMethod->sum('income');
        $resultObject->completed_orders = $orderBaseQueryByPaymentMethod->sum('count');

        $resultObject->pickup_delivery_income = $orderRequestQuery->sum('O_REQ.amount');
        $resultObject->actual_pickup_delivery_income = $orderRequestQuery->sum('O_REQ.actual_estimate');

        $resultObject->discounts = (clone $discountsQuery)->sum('users_discounts.discount_earned');
        $resultObject->discounts_count = (clone $discountsQuery)->count();

        $resultObject->pending_income = (clone $baseQuery)->where('status', ORDER_STATUS_PENDING)->sum(DB::raw("amount + pickup_cost + delivery_cost"));
        $resultObject->pending_orders = (clone $baseQuery)->where('status', ORDER_STATUS_PENDING)->count();
        $resultObject->new_customers = (clone $newCustomerQuery)->count();
        $resultObject->companies = Company::all();
        $resultObject->companies_count = $resultObject->companies->count();
        $resultObject->locations = Location::getAllowed();
        $resultObject->locations_count = $resultObject->locations->count();
        $resultObject->customers_count = User::whereNull('deleted_at')->count();
        if($showOrders) {
            $resultObject->orders = (clone $baseQuery);
        }
        if($showTransactions){
            $resultObject->transactions = (clone $baseTransactionsQuery)->where('transaction_type_id', TransactionType::ORDER_PAYMENT_ID);
        }
        $resultObject->monthly_labels = $month_label_lists;

        return $resultObject;
    }

    public function dashboardStatistics()
    {
        $mainOrdersBaseQuery = $this->getOrderBaseQuery();
        $orderBaseQuery = $this->getOrderBaseQuery()->where('status', ORDER_STATUS_COMPLETED);

        $kwikOrdersQuery = KwikPickupsAndDelivery::from('kwik_pickups_and_deliveries as KPD')
            ->leftJoin('orders', function (JoinClause $join) {
                $join->on('orders.id', '=', 'KPD.order_id');
                if($this->company){
                    $join = $join->where('orders.company_id', $this->company);
                }
                if($this->location){
                    $join = $join->where('orders.location_id', $this->location);
                }
            })
            ->leftJoin('locations', 'locations.id', '=', 'orders.location_id')
            ->whereIn('KPD.job_status', [OrderRequestStatus::DROPPED_OFF, OrderRequestStatus::ORDER_DELIVERED]);

        if($this->company){
            $kwikOrdersQuery->where('orders.company_id', $this->company);
        }
        if($this->location){
            $kwikOrdersQuery->where('orders.location_id', $this->location);
        }

        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $currentYear = now()->year;
        $currentMonthName = now()->monthName;
        $today = now()->toDateString();
        $monthsInYear = $monthsInYear = [
            ['month' => 1, 'month_name' => 'January', 'amount' => 0],
            ['month' => 2, 'month_name' => 'February', 'amount' => 0],
            ['month' => 3, 'month_name' => 'March', 'amount' => 0],
            ['month' => 4, 'month_name' => 'April', 'amount' => 0],
            ['month' => 5, 'month_name' => 'May', 'amount' => 0],
            ['month' => 6, 'month_name' => 'June', 'amount' => 0],
            ['month' => 7, 'month_name' => 'July', 'amount' => 0],
            ['month' => 8, 'month_name' => 'August', 'amount' => 0],
            ['month' => 9, 'month_name' => 'September', 'amount' => 0],
            ['month' => 10, 'month_name' => 'October', 'amount' => 0],
            ['month' => 11, 'month_name' => 'November', 'amount' => 0],
            ['month' => 12, 'month_name' => 'December', 'amount' => 0],
        ];
        $incomeReportGroupedByMonth = (clone $orderBaseQuery)->whereYear('completed_at', $currentYear)->select([
            DB::raw('SUM(amount) as income'),
            DB::raw('MONTH(completed_at) as month'),
            DB::raw('MONTHNAME(completed_at) as month_name')
        ])->groupBy('month')->get()->pluck('income', 'month')->toArray();

        $kwikOrdersGroupedByMonth = (clone $kwikOrdersQuery)
            ->where('KPD.is_paid_for', true)
            ->whereYear('KPD.completed_datetime', $currentYear)
            ->select([
                DB::raw('SUM(KPD.actual_amount_paid) as pickup_delivery_income'),
                DB::raw('SUM(orders.pickup_cost + orders.delivery_cost) as old_pickup_delivery_income'),
                DB::raw('MONTH(KPD.date) as month'),
                DB::raw('MONTHNAME(KPD.date) as month_name')
            ])->groupBy('month')->pluck('pickup_delivery_income', 'month')->toArray();

        $monthlyReportForChart = collect($monthsInYear)->map(function ($entry) use ($incomeReportGroupedByMonth, $kwikOrdersGroupedByMonth){
            $month = $entry['month'];
            return [
                'label' => $entry['month_name'],
                'income' => isset($incomeReportGroupedByMonth[$month]) ? $incomeReportGroupedByMonth[$month] : 0,
                'pickup_delivery_income' => isset($kwikOrdersGroupedByMonth[$month]) ? $kwikOrdersGroupedByMonth[$month] : 0
            ];
        });
        $completedKwikOrdersQuery = (clone $kwikOrdersQuery)->where('is_paid_for', true);
        $pickupDeliveryStats = (object)[
            'total' => (object)[
                'amountForKwik' => (clone $kwikOrdersQuery)->sum('KPD.actual_amount_paid'),
                'amountPending' => (clone $kwikOrdersQuery)->where('is_paid_for', false)->sum('KPD.actual_amount_paid'),
                'amountReceived' => (clone $completedKwikOrdersQuery)->sum('KPD.actual_amount_paid'),
            ],
            'this_month' => (object) [
                'amountForKwik' => (clone $kwikOrdersQuery)->whereBetween('KPD.date', [$startOfMonth, $endOfMonth])->sum('KPD.actual_amount_paid'),
                'amountPending' => (clone $kwikOrdersQuery)->whereBetween('KPD.date', [$startOfMonth, $endOfMonth])->where('is_paid_for', false)->sum('KPD.actual_amount_paid'),
                'amountReceived' => (clone $completedKwikOrdersQuery)->whereBetween('KPD.date', [$startOfMonth,$endOfMonth])->sum('KPD.actual_amount_paid'),
            ],
            'today' => (object) [
                'amountForKwik' => (clone $kwikOrdersQuery)->whereDate('KPD.date', $today)->sum('KPD.actual_amount_paid'),
                'amountPending' => (clone $kwikOrdersQuery)->whereDate('KPD.date', $today)->where('is_paid_for', false)->sum('KPD.actual_amount_paid'),
                'amountReceived' => (clone $completedKwikOrdersQuery)->whereDate('KPD.date', $today)->sum('KPD.actual_amount_paid')
            ]
        ];
        $topCustomers = (clone $orderBaseQuery)->leftJoin('users', 'users.id', 'orders.user_id')
            ->select(['users.*', DB::raw("COUNT(orders.id) as orders_count"), DB::raw("(SUM(amount) + SUM('pickup_cost') + SUM('delivery_cost')) as total")])
            ->orderBy('total', 'DESC')
            ->groupBy('users.id')->limit(5)->get();

        $allOrdersByPaymentStatus = (clone $mainOrdersBaseQuery)->select([
            'orders.status as order_status',
            DB::raw('SUM(orders.amount) as income'),
            DB::raw('COUNT(orders.id) as orders_count')
        ])->groupBy('orders.status')->get();
        $totalOrdersBreakdown = (object)[
            'pending_income' => (clone $allOrdersByPaymentStatus)->where('order_status', ORDER_STATUS_PENDING)->sum('income'),
            'pending_orders' => (clone $allOrdersByPaymentStatus)->where('order_status', ORDER_STATUS_PENDING)->sum('orders_count'),
            'received_income' => (clone $allOrdersByPaymentStatus)->where('order_status', ORDER_STATUS_COMPLETED)->sum('income'),
            'completed_orders' => (clone $allOrdersByPaymentStatus)->where('order_status', ORDER_STATUS_COMPLETED)->sum('orders_count')
        ];

        $pendingOrdersThisMonth = (clone $mainOrdersBaseQuery)->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth])
            ->where('orders.status', ORDER_STATUS_PENDING);

        $completedOrdersThisMonth = (clone $mainOrdersBaseQuery)->whereBetween('orders.completed_at', [$startOfMonth, $endOfMonth])
            ->where('orders.status', ORDER_STATUS_COMPLETED);
        $ordersBreakdownThisMonth = (object)[
            'pending_income' => (clone $pendingOrdersThisMonth)->sum('amount'),
            'pending_orders' => (clone $pendingOrdersThisMonth)->count('orders.id'),
            'received_income' => (clone $completedOrdersThisMonth)->sum('amount'),
            'completed_orders' => (clone $completedOrdersThisMonth)->count('orders.id')
        ];

        $pendingOrdersToday = (clone $mainOrdersBaseQuery)->whereDate('orders.created_at', $today)
            ->where('orders.status', ORDER_STATUS_PENDING);

        $completedOrdersToday = (clone $mainOrdersBaseQuery)->whereDate('orders.completed_at', $today)
            ->where('orders.status', ORDER_STATUS_COMPLETED);
        $ordersBreakdownToday = (object)[
            'pending_income' => (clone $pendingOrdersToday)->sum('amount'),
            'pending_orders' => (clone $pendingOrdersToday)->count('orders.id'),
            'received_income' => (clone $completedOrdersToday)->sum('amount'),
            'completed_orders' => (clone $completedOrdersToday)->count('orders.id')
        ];

        $firstOrder = (clone $orderBaseQuery)->orderBy('created_at')->first();
        $latestOrder = (clone $orderBaseQuery)->orderByDesc('created_at')->first();

        $firstKwikOrder = (clone $kwikOrdersQuery)->orderBy('KPD.date')->first();
        $latestKwikOrder = (clone $kwikOrdersQuery)->orderByDesc('KPD.date')->first();
        return (object)[
            'all_users' => User::setEagerLoads([])->count(),
            'all_time_income' => (clone $orderBaseQuery)->sum('amount'),
            'sales_today' => (clone $orderBaseQuery)->whereDate('completed_at', $today)->sum('amount'),
            'sales_this_month' => (clone $orderBaseQuery)->whereDate('completed_at', '>=', $startOfMonth)
                ->whereDate('completed_at', '<=', $endOfMonth)->sum('amount'),
            'totalOrdersBreakdown' => $totalOrdersBreakdown,
            'ordersBreakdownThisMonth' => $ordersBreakdownThisMonth,
            'ordersBreakdownToday' => $ordersBreakdownToday,
            'monthly_income_report' => $monthlyReportForChart,
            'top_customers' => $topCustomers,
            'pickupDeliveryStats' => $pickupDeliveryStats,
            'current_year' => $currentYear,
            'current_month' => $currentMonthName,
            'firstOrderDate' => $firstOrder ? $firstOrder->created_at->toDateString() : $today,
            'latestOrderDate' => $latestOrder ? $latestOrder->created_at->toDateString() : $today,
            'firstKwikOrderDate' => $firstKwikOrder ? $firstKwikOrder->date->toDateString() : $today,
            'latestKwikOrderDate' => $latestKwikOrder ? $latestKwikOrder->date->toDateString() : $today,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth
        ];

    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getOrderBaseQuery()
    {
        $baseQuery = Order::setEagerLoads([]);
        if($this->company){
            $baseQuery = $baseQuery->where('orders.company_id', $this->company);
        }
        if($this->location){
            $baseQuery = $baseQuery->where('orders.location_id', $this->location);
        }
        return $baseQuery;

    }
}
