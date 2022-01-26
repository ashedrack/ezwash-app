<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Classes\KwikRequestsHandler;
use App\Classes\Meta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderRequest
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $phone
 * @property int $order_id
 * @property int $address_id
 * @property Carbon $time
 * @property string $note
 * @property bool $scheduled
 * @property int $order_request_status_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $location_id
 * @property string $kwik_order_id
 * @property int $temp_order_request_id
 * @property float $amount
 * @property float $actual_estimate
 * @property string $kwik_job_ids
 * @property string $job_details_payload
 * @property bool $has_pickup
 * @property bool $has_delivery
 * @property int $order_request_type_id
 * @property int $pickup_task_status
 * @property int $temp_next_delivery_job_status
 * @property int $delivery_task_status
 * @property int $temp_next_pickup_job_status
 *
 * @property Order $order
 * @property UserAddress $address
 * @property Location $store_location
 * @property OrdersStatus $status
 * @property array $getOrderLatitudeAndLongitude
 * @property TempOrderRequest $temp_order_request
 * @property boolean $isDelivery
 * @property boolean $isPickup
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class OrderRequest extends Model
{
    protected $table = 'order_requests';

    protected $casts = [
        'amount' => 'float',
        'actual_estimate' => 'float',
        'user_id' => 'int',
        'order_id' => 'int',
        'address_id' => 'int',
        'location_id' => 'int',
        'scheduled' => 'bool',
        'has_pickup' => 'bool',
        'has_delivery' => 'bool',
        'order_request_status_id' => 'int',
        'order_request_type_id' => 'int',
        'temp_order_request_id' => 'int',
        'temp_next_delivery_job_status' => 'int',
        'pickup_task_status' => 'int',
        'temp_next_pickup_job_status' => 'int',
        'delivery_task_status' => 'int'
    ];

    protected $dates = [
        'time',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'order_id',
        'address_id',
        'time',
        'note',
        'scheduled',
        'order_request_status_id',
        'location_id',
        'kwik_order_id',
        'temp_order_request_id',
        'amount',
        'actual_estimate',
        'kwik_job_ids', // comma separated delivery and pickup job_ids "1353,1254"
        'has_pickup',
        'has_delivery',
        'order_request_type_id',
        'temp_next_delivery_job_status',
        'pickup_task_status',
        'temp_next_pickup_job_status',
        'delivery_task_status',
        'job_details_payload'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeline()
    {
        return $this->hasMany(OrdersTimeline::class, 'order_request_id');
    }

    public function status()
    {
        return $this->belongsTo(OrderRequestStatus::class);
    }

    public function store_location()
    {
        return $this->belongsTo(Location::class, 'location_id')->withoutGlobalScopes();
    }

    public function order_request_type()
    {
        return $this->belongsTo(OrderRequestType::class);
    }

    public function order_request_status()
    {
        return $this->belongsTo(OrderRequestStatus::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class)->withoutGlobalScopes();
    }

    /**
     * Get's Timelines
     *
     * @return array
     */
    public function timelineForMobile()
    {
        /**
         * If it is a delivery task with no matching pickup
         */
        if ($this->order_request_type_id === OrderRequestType::DELIVERY) {
            return [
                [
                    'position' => 1,
                    'status' => $this->order->status === Meta::ORDER_STATUS_COMPLETED,
                    'display_name' => 'Paid',
                ],
                [
                    'position' => 2,
                    'status' => $this->statusExistsInTimeline(OrderRequestStatus::DELIVERY_STARTED),
                    'display_name' => 'Assigned',
                ],
                [
                    'position' => 3,
                    'status' => $this->statusExistsInTimeline(OrderRequestStatus::PICKED_UP_FOR_DELIVERY),
                    'display_name' => 'Picked Up',
                ],
                [
                    'position' => 4,
                    'status' => $this->statusExistsInTimeline(OrderRequestStatus::ORDER_DELIVERED),
                    'display_name' => 'Delivered',
                ]
            ];
        } else {
            $thirdItemInTimeline = $this->droppedOffProcessingOrProcessed();
            $deliveredOrCollected = $this->deliveredOrCollected();
            $order = $this->order;
            return [
                [
                    'position' => 1,
                    'status' => $this->statusExistsInTimeline(OrderRequestStatus::PICKUP_STARTED),
                    'display_name' => 'Assigned',
                ],
                [
                    'position' => 2,
                    'status' => $this->statusExistsInTimeline(OrderRequestStatus::PICKED_UP),
                    'display_name' => 'Picked Up',
                ],
                [
                    'position' => 3,
                    'status' => $thirdItemInTimeline['status'],
                    'display_name' => $thirdItemInTimeline['display_name'],
                ],
                [
                    'position' => 4,
                    'status' => (!empty($order) && $order->status === Meta::ORDER_STATUS_COMPLETED),
                    'display_name' => 'Paid',
                ],
                [
                    'position' => 5,
                    'status' => $deliveredOrCollected['status'],
                    'display_name' => $deliveredOrCollected['display_name'],
                ]
            ];
        }
    }

    public function statusExistsInTimeline($status)
    {
        return $this->timeline()->where('status_id', $status)->exists();
    }

    public function droppedOffProcessingOrProcessed()
    {
        $order = $this->order;
        $displayName = 'Dropped Off';
        $status = false;

        if (!empty($order) && $order->amount > 0) {
            $displayName = 'Processed';
            $status = true;
        } elseif ($this->statusExistsInTimeline(OrderRequestStatus::WASH_IN_PROGRESS)) {
            $displayName = 'Processing';
            $status = true;
        } elseif ($this->statusExistsInTimeline(OrderRequestStatus::DROPPED_OFF)) {
            $status = true;
        }
        return [
            'display_name' => $displayName,
            'status' => $status,
        ];
    }

    /**
     * Determines what to display on the last position of the timeline view (collected or delivered)
     * - also determines if the status is true or false
     *
     * @return array
     */
    public function deliveredOrCollected()
    {
        $order = $this->order;
        $displayName = 'Collected';
        if ($this->hasDeliveryTask()) {
            $displayName = 'Delivered';
            $status = $this->statusExistsInTimeline(OrderRequestStatus::ORDER_DELIVERED);
        } else {
            $status = (!empty($order) && $order->collected);
        }
        return [
            'status' => $status,
            'display_name' => $displayName,
        ];
    }

    public function hasDeliveryTask()
    {
        $order = $this->order;
        return (!empty($order) && $order->hasDeliveryRequest());
    }

    public function matchRequestStatusToKwikStatus($pickJobStatus, $deliveryJobStatus)
    {
        /**
         * Theses statuses help to match the delivery status sent from kwik to the order request status
         * - Since a single order on kwik has two jobs(order-pickup & order-delivery)
         */
        if ($this->order_request_type_id === OrderRequestType::PICKUP) {
            //Check if the delivery task is upcoming meaning order has not been picked up from the customer
            if ($this->order_request_status_id !== OrderRequestStatus::PICKED_UP && in_array($deliveryJobStatus, [KwikRequestsHandler::KW_TASK_STATUS_UNASSIGNED, KwikRequestsHandler::KW_TASK_STATUS_UPCOMING, KwikRequestsHandler::KW_TASK_STATUS_ACCEPTED])) {
                $pickupMatches = [
                    KwikRequestsHandler::KW_TASK_STATUS_UPCOMING => OrderRequestStatus::PICKUP_REQUESTED,
                    KwikRequestsHandler::KW_TASK_STATUS_DECLINE => OrderRequestStatus::PICKUP_REQUESTED,
                    KwikRequestsHandler::KW_TASK_STATUS_UNASSIGNED => OrderRequestStatus::PICKUP_REQUESTED,
                    KwikRequestsHandler::KW_TASK_STATUS_CANCEL => OrderRequestStatus::PICKUP_CANCELED,
                    KwikRequestsHandler::KW_TASK_STATUS_ACCEPTED => OrderRequestStatus::PICKUP_STARTED,
                    KwikRequestsHandler::KW_TASK_STATUS_STARTED => OrderRequestStatus::PICKUP_STARTED,
                    KwikRequestsHandler::KW_TASK_STATUS_ARRIVED => OrderRequestStatus::PICKUP_STARTED,
                    KwikRequestsHandler::KW_TASK_STATUS_ENDED => OrderRequestStatus::PICKED_UP,
                    KwikRequestsHandler::KW_TASK_STATUS_FAILED => OrderRequestStatus::REQUEST_FAILED,
                    KwikRequestsHandler::KW_TASK_STATUS_DELETED => OrderRequestStatus::REQUEST_FAILED,
                ];
                return $pickupMatches[$pickJobStatus];
            }
            $deliveryMatches = [
                KwikRequestsHandler::KW_TASK_STATUS_UPCOMING => OrderRequestStatus::PICKED_UP,
                KwikRequestsHandler::KW_TASK_STATUS_ACCEPTED => OrderRequestStatus::PICKED_UP,
                KwikRequestsHandler::KW_TASK_STATUS_STARTED => OrderRequestStatus::PICKED_UP,
                KwikRequestsHandler::KW_TASK_STATUS_ENDED => OrderRequestStatus::DROPPED_OFF,
                KwikRequestsHandler::KW_TASK_STATUS_ARRIVED => OrderRequestStatus::DROPPED_OFF,
                KwikRequestsHandler::KW_TASK_STATUS_FAILED => OrderRequestStatus::REQUEST_FAILED,
                KwikRequestsHandler::KW_TASK_STATUS_DELETED => OrderRequestStatus::REQUEST_FAILED,
                KwikRequestsHandler::KW_TASK_STATUS_CANCEL => OrderRequestStatus::PICKUP_CANCELED,
            ];
            return $deliveryMatches[$deliveryJobStatus];
        }

        $deliveryMatches = [
            KwikRequestsHandler::KW_TASK_STATUS_STARTED => OrderRequestStatus::PICKED_UP_FOR_DELIVERY,
            KwikRequestsHandler::KW_TASK_STATUS_ENDED => OrderRequestStatus::ORDER_DELIVERED,
            KwikRequestsHandler::KW_TASK_STATUS_ACCEPTED => OrderRequestStatus::PICKED_UP_FOR_DELIVERY,
            KwikRequestsHandler::KW_TASK_STATUS_FAILED => OrderRequestStatus::REQUEST_FAILED,
            KwikRequestsHandler::KW_TASK_STATUS_ARRIVED => OrderRequestStatus::ORDER_DELIVERED,
            KwikRequestsHandler::KW_TASK_STATUS_CANCEL => OrderRequestStatus::DELIVERY_CANCELLED,
            KwikRequestsHandler::KW_TASK_STATUS_DELETED => OrderRequestStatus::REQUEST_FAILED,
        ];
        if ($this->order_request_status_id !== OrderRequestStatus::PICKED_UP_FOR_DELIVERY && in_array($deliveryJobStatus, [KwikRequestsHandler::KW_TASK_STATUS_UNASSIGNED, KwikRequestsHandler::KW_TASK_STATUS_UPCOMING, KwikRequestsHandler::KW_TASK_STATUS_ACCEPTED])) {
            $pickupMatches = [
                KwikRequestsHandler::KW_TASK_STATUS_UPCOMING => OrderRequestStatus::DELIVERY_REQUESTED,
                KwikRequestsHandler::KW_TASK_STATUS_DECLINE => OrderRequestStatus::DELIVERY_REQUESTED,
                KwikRequestsHandler::KW_TASK_STATUS_UNASSIGNED => OrderRequestStatus::DELIVERY_REQUESTED,
                KwikRequestsHandler::KW_TASK_STATUS_CANCEL => OrderRequestStatus::DELIVERY_CANCELLED,
                KwikRequestsHandler::KW_TASK_STATUS_ACCEPTED => OrderRequestStatus::DELIVERY_STARTED,
                KwikRequestsHandler::KW_TASK_STATUS_STARTED => OrderRequestStatus::DELIVERY_STARTED,
                KwikRequestsHandler::KW_TASK_STATUS_ARRIVED => OrderRequestStatus::DELIVERY_STARTED,
                KwikRequestsHandler::KW_TASK_STATUS_ENDED => OrderRequestStatus::PICKED_UP_FOR_DELIVERY,
                KwikRequestsHandler::KW_TASK_STATUS_FAILED => OrderRequestStatus::REQUEST_FAILED,
                KwikRequestsHandler::KW_TASK_STATUS_DELETED => OrderRequestStatus::REQUEST_FAILED,
            ];
            return $pickupMatches[$pickJobStatus];
        }
        $deliveryMatches = [
            KwikRequestsHandler::KW_TASK_STATUS_STARTED => OrderRequestStatus::PICKED_UP_FOR_DELIVERY,
            KwikRequestsHandler::KW_TASK_STATUS_ENDED => OrderRequestStatus::ORDER_DELIVERED,
            KwikRequestsHandler::KW_TASK_STATUS_ACCEPTED => OrderRequestStatus::PICKED_UP_FOR_DELIVERY,
            KwikRequestsHandler::KW_TASK_STATUS_FAILED => OrderRequestStatus::REQUEST_FAILED,
            KwikRequestsHandler::KW_TASK_STATUS_ARRIVED => OrderRequestStatus::ORDER_DELIVERED,
            KwikRequestsHandler::KW_TASK_STATUS_CANCEL => OrderRequestStatus::DELIVERY_CANCELLED,
            KwikRequestsHandler::KW_TASK_STATUS_DELETED => OrderRequestStatus::REQUEST_FAILED,
        ];
        return $deliveryMatches[$deliveryJobStatus];
    }

    public function getOrderLatitudeAndLongitude($pickupJob, $deliveryJob)
    {
        $orderRequestStatus = $this->matchRequestStatusToKwikStatus($pickupJob['job_status'], $deliveryJob['job_status']);
        if (!in_array($orderRequestStatus, OrderRequestStatus::trackableStatuses())) {
            return [
                'trackable' => false,
            ];
        }
        $pickupLocation = ($this->isPickup()) ? $this->address : $this->store_location;
        $deliveryLocation = ($this->isPickup()) ? $this->store_location : $this->address;
        $currentLocation = null;
        $distance = null;
        if ($orderRequestStatus === OrderRequestStatus::PICKUP_STARTED || $orderRequestStatus === OrderRequestStatus::DELIVERY_STARTED) {
            $currentLocation = ['latitude' => $pickupJob['job_latitude'], 'longitude' => $pickupJob['job_longitude']];
            $distance = distanceBetweenTwoPoints($pickupLocation->latitude, $pickupLocation->longitude, $currentLocation['latitude'], $currentLocation['longitude'], "K");
        } else {
            $currentLocation = ['latitude' => $deliveryJob['job_latitude'], 'longitude' => $deliveryJob['job_longitude']];

            $distance = distanceBetweenTwoPoints($deliveryLocation->latitude, $deliveryLocation->longitude, $currentLocation['latitude'], $currentLocation['longitude'], "K");
        }
        return [
            'trackable' => true,
            'pickup_point' => [
                'latitude' => $pickupLocation->latitude,
                'longitude' => $pickupLocation->longitude
            ],
            'delivery_point' => [
                'latitude' => $deliveryLocation->latitude,
                'longitude' => $deliveryLocation->longitude
            ],
            'current_location' => $currentLocation,
            'distance' => $distance
        ];
    }

    public function isDelivery()
    {
        return $this->order_request_type_id === OrderRequestType::DELIVERY;
    }

    public function isPickup()
    {
        return $this->order_request_type_id === OrderRequestType::PICKUP;
    }

    public function kwik_job()
    {
        return $this->hasOne(KwikPickupsAndDelivery::class);
    }

    /**
     * @param integer $userID
     * @param boolean $onlyProcessing
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public static function getUserOrderRequests($userID, $onlyProcessing = true)
    {
        // Fetch all pickups and include id of the accompanying delivery request if any
        $pickupsWithDeliveries = DB::table('order_requests AS p')
            ->leftJoin('order_requests AS d', function ($join) {
                $join->on('p.order_id', '=', 'd.order_id')
                    ->where('p.order_request_type_id', OrderRequestType::PICKUP)
                    ->whereRaw('p.id < d.id')
                    ->where('d.order_request_type_id', OrderRequestType::DELIVERY);
            })
            ->leftJoin('orders', 'orders.id', 'p.order_id')
            ->select([
                'p.*',
                'd.id as delivery_request_id',
                'd.time as delivery_request_time',
                'd.created_at as delivery_request_created_at',
            ])
            ->whereRaw('p.user_id = ?', [$userID])
            ->whereIn('p.order_request_status_id', [
                OrderRequestStatus::PICKUP_REQUESTED,
                OrderRequestStatus::PICKUP_STARTED,
                OrderRequestStatus::PICKED_UP,
                OrderRequestStatus::DROPPED_OFF,
                OrderRequestStatus::WASH_IN_PROGRESS,
                OrderRequestStatus::READY_FOR_COLLECTION
            ]);
        if ($onlyProcessing) {
            $pickupsWithDeliveries = $pickupsWithDeliveries->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('d.id')
                        ->whereNotNull('d.order_request_status_id')
                        ->where('d.order_request_status_id', '<>', OrderRequestStatus::DELIVERY_CANCELLED);
                })->orWhere(function ($q) {
                    $q->whereNull('d.id')->where('orders.status', Meta::ORDER_STATUS_PENDING);
                });
            });
        }

        // Fetch all only-delivery orders
        $onlyDeliveries = DB::table('order_requests')->where('user_id', $userID)
            ->where(function ($q) {
                $q->where('order_request_type_id', OrderRequestType::PICKUP)
                    ->where('has_delivery', false);
            })->select([
                '*',
                DB::raw('null as delivery_request_id'),
                DB::raw('null as delivery_request_time'),
                DB::raw('null as delivery_request_created_at')
            ]);
        if ($onlyProcessing) {
            $onlyDeliveries = $onlyDeliveries
                ->whereNotNull('order_requests.order_request_status_id')
                ->where('order_requests.order_request_status_id', '<>', OrderRequestStatus::DELIVERY_CANCELLED);
        }
        return $onlyDeliveries->union($pickupsWithDeliveries)
            ->orderBy('created_at', 'DESC');

        /**
         * (select *, null as delivery_request_id from `order_requests` where `user_id` = $userID
         *  and (`order_request_type_id` = 1 and `has_delivery` = false)
         * ) union (
         *  select `p`.*, `d`.`id` as `delivery_request_id` from `order_requests` as `p`
         *  left join `order_requests` as `d` on `p`.`order_id` = `d`.`order_id`
         *      and `p`.`order_request_type_id` = 1
         *      and p.id < d.id
         *      and `d`.`order_request_type_id` = 2
         *  where p.`user_id` = 1
         * )
         */


    }

    public function temp_order_request()
    {
        return $this->belongsTo(TempOrderRequest::class);
    }

    public function pickupTaskStatus()
    {
        return $this->belongsTo(KwikTaskStatus::class, 'pickup_task_status');
    }

    public function deliveryTaskStatus()
    {
        return $this->belongsTo(KwikTaskStatus::class, 'delivery_task_status');
    }

}
