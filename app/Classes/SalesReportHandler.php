<?php


namespace App\Classes;

use App\Models\KwikPickupsAndDelivery;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderRequestStatus;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class SalesReportHandler
{

    /**
     * @var array
     */
    public static $defaultStats = [
        'totalSales' => 0,
        'cashTransactions' => 0,
        'cardTransactions' => 0,
        'posTransactions' => 0,
        'cardTransactionsCount' => 0
    ];
    /**
     * @var bool
     */
    protected $is_daily;
    protected $companyID;


    /***
     * SalesReportHandler constructor.
     * @param int $companyID
     * @param bool $is_daily
     */
    public function __construct($companyID, $is_daily = true)
    {
        $this->is_daily = $is_daily;
        $this->companyID = $companyID;
    }

    /***
     * @return object
     */
    public function ReportStatistics()
    {
        if ($this->is_daily) {
            $startDate = now()->toDateString();
            $endDate = now()->toDateString();
        } else {
            $startDate = now()->subMonth()->startOfMonth()->toDateString();
            $endDate = now()->subMonth()->endOfMonth()->toDateString();
        }

        $baseQuery = Transaction::from('transactions as TR')
            ->join('orders', function (JoinClause $join) {
                $join->on('orders.id', 'TR.order_id');
            })
            ->leftJoin('locations', 'locations.id', '=', 'orders.location_id')
            ->where('TR.transaction_status_id', TransactionStatus::COMPLETED)
            ->whereDate('TR.created_at', '>=', $startDate)
            ->whereDate('TR.created_at', '<=', $endDate);


        $orderBaseQueryByPaymentMethodAndLocation = (clone $baseQuery)
            ->select([
                'locations.id as location_id',
                'TR.transaction_payment_method_id as payment_method_id',
                DB::raw('SUM(orders.amount) as income'),
                DB::raw('COUNT(TR.transaction_payment_method_id) as count'),
                //  DB::raw('SUM(orders.pickup_cost + orders.delivery_cost) as pickup_delivery_income'),
            ])->groupBy(['location_id', 'TR.transaction_payment_method_id'])->get();

        $kwikOrdersGroupedByLocation = KwikPickupsAndDelivery::from('kwik_pickups_and_deliveries as KPD')
            ->leftJoin('orders', 'KPD.order_id', '=', 'orders.id')
            ->leftJoin('locations', 'locations.id', '=', 'orders.location_id')
            ->whereIn('job_status', [OrderRequestStatus::DROPPED_OFF, OrderRequestStatus::ORDER_DELIVERED])
            ->groupBy(['orders.location_id'])->get();

        $locationsStats = Location::where('company_id', $this->companyID)->get()
            ->map(function ($location) use ($orderBaseQueryByPaymentMethodAndLocation, $kwikOrdersGroupedByLocation){

            $orderBaseQueryByPaymentMethod = $orderBaseQueryByPaymentMethodAndLocation->where('location_id', $location->id);
            $kwikOrdersQuery = $kwikOrdersGroupedByLocation->where('orders.location_id', $location->id);

            $total_sales = (clone $orderBaseQueryByPaymentMethod)->sum('income');
            $cashTransactions = (clone $orderBaseQueryByPaymentMethod)->where('payment_method_id', PaymentMethod::CASH_PAYMENT)->first();
            $cardTransactions = (clone $orderBaseQueryByPaymentMethod)->where('payment_method_id', PaymentMethod::CARD_PAYMENT)->first();
            $posTransactions = (clone $orderBaseQueryByPaymentMethod)->where('payment_method_id', PaymentMethod::POS_PAYMENT)->first();

            $pickupsAndDeliveriesAll = $kwikOrdersQuery->count();
            $pickupsAndDeliveriesTotalAmount = $kwikOrdersQuery->sum('KPD.actual_amount_paid');
            $pickupsAndDeliveriesPaid = $kwikOrdersQuery->where('KPD.is_paid_for', true)->sum('KPD.actual_amount_paid');
            $pickupsAndDeliveriesPaidCount = $kwikOrdersQuery->where('KPD.is_paid_for', true)->count();

            return (object)[
                'location_id' => $location->id,
                'name' => $location->name,
                'statistics_data' => (object) [
                    'totalSales' => $total_sales,
                    'cardTransactions' => $cardTransactions ? $cardTransactions->income : 0,
                    'cashTransactions' => $cashTransactions ? $cashTransactions->income : 0,
                    'posTransactions' => $posTransactions ? $posTransactions->income: 0,

                    'cardTransactionsCount' => $cardTransactions ? $cardTransactions->count : 0,
                    'cashTransactionsCount' => $cashTransactions ? $cashTransactions->count : 0,
                    'posTransactionsCount' => $posTransactions ? $posTransactions->count : 0,

                    'pickupsAndDeliveriesTotalAmount' => $pickupsAndDeliveriesTotalAmount,
                    'pickupsAndDeliveriesTotalCount' => $pickupsAndDeliveriesAll,
                    'pickupsAndDeliveriesPaid' => $pickupsAndDeliveriesPaid,
                    'pickupsAndDeliveriesPaidCount' => $pickupsAndDeliveriesPaidCount
                ]
            ];
        });

        $cashTransactions = (clone $orderBaseQueryByPaymentMethodAndLocation)->where('payment_method_id', PaymentMethod::CASH_PAYMENT);
        $cardTransactions = (clone $orderBaseQueryByPaymentMethodAndLocation)->where('payment_method_id', PaymentMethod::CARD_PAYMENT);
        $posTransactions = (clone $orderBaseQueryByPaymentMethodAndLocation)->where('payment_method_id', PaymentMethod::POS_PAYMENT);

        $newUsers = User::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)->count();

        $pickupsAndDeliveriesTotalAmount = $kwikOrdersGroupedByLocation->sum('KPD.actual_amount_paid');
        $pickupsAndDeliveriesTotalCount = $kwikOrdersGroupedByLocation->count();
        $pickupsAndDeliveriesPaid = $kwikOrdersGroupedByLocation->where('KPD.is_paid_for', true)->sum('KPD.actual_amount_paid');
        $pickupsAndDeliveriesPaidCount = $kwikOrdersGroupedByLocation->where('KPD.is_paid_for', true)->count();

        return (object) [
            'totalSales' => (clone $baseQuery)->sum('TR.amount'),
            'newUsers' => $newUsers,
            'cardTransactions' => $cardTransactions->isNotEmpty() ? $cardTransactions->sum('income') : 0,
            'cashTransactions' => $cashTransactions->isNotEmpty() ? $cashTransactions->sum('income') : 0,
            'posTransactions' => $posTransactions->isNotEmpty() ? $posTransactions->sum('income'): 0,

            'cardTransactionsCount' => $cardTransactions->isNotEmpty() ? $cardTransactions->sum('count') : 0,
            'cashTransactionsCount' => $cashTransactions->isNotEmpty() ? $cashTransactions->sum('count') : 0,
            'posTransactionsCount' => $posTransactions->isNotEmpty() ? $posTransactions->sum('count') : 0,

            'pickupsAndDeliveriesTotalAmount' => $pickupsAndDeliveriesTotalAmount,
            'pickupsAndDeliveriesTotalCount' => $pickupsAndDeliveriesTotalCount,
            'pickupsAndDeliveriesPaid' => $pickupsAndDeliveriesPaid,
            'pickupsAndDeliveriesPaidCount' => $pickupsAndDeliveriesPaidCount,

            'locations_statistics' => $locationsStats
        ];
    }

}
