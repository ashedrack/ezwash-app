<?php

namespace App\Classes;

use App\Exceptions\KwikRequestException;
use App\Http\Controllers\CustomerApi\PaymentsController;
use App\Jobs\CreateOrUpdateKwikPickupsAndDeliveries;
use App\Models\Employee;
use App\Models\Location;
use App\Models\LoyaltyOffer;
use App\Models\Order;
use App\Models\OrderRequestStatus;
use App\Models\OrdersDiscount;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Notifications\ReadyForPickupNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class OrderRequestHandler
{
    protected $order;
    protected $requestParams;
    protected $authUser;
    protected $customer;
    protected $location;

    /**
     * OrderRequestHandler constructor.
     *
     * @param array $requestParams
     * @param Employee|Authenticatable $authUser
     * @param Order|null $order
     */
    public function __construct($requestParams, $authUser, Order $order = null)
    {
        $this->order = $order;
        $this->requestParams = $requestParams;
        $this->authUser = $authUser;
        if(is_null($order)){
            $this->customer = User::find($requestParams['user_id']);
        }else{
            $this->customer= $order->user;
        }
        $this->location = ($order)? Location::find($order->location_id): Location::find($requestParams['location']);
    }

    /**
     * @return array
     */
    public function collectOrderServices()
    {
        $requestServices = collect($this->requestParams['services'])->pluck('quantity', 'id')->toArray();
        $services = Service::whereIn('id', array_keys($requestServices))->get();
        return array_map(function ($s) use ($requestServices) {
            $quantity = $requestServices[$s['id']];
            $total = $s['price'] * $quantity;
            return [
                'service_id' => $s['id'],
                'quantity' => $quantity,
                'price' => $s['price'],
                'total' => $total
            ];
        }, $services->toArray());
    }

    /**
     * @return integer
     */
    public function getPaymentMethod()
    {
        return (isset($this->requestParams['payment_method']) && !is_null($this->requestParams['payment_method']))? PaymentMethod::where('name', $this->requestParams['payment_method'])->first()->id: null;
    }

    public function getUserDiscount()
    {
        if($this->order){
            return $this->customer->discounts()
                ->where('status', Meta::UNUSED_DISCOUNT)
                ->where('created_at' , '<', $this->order->created_at)
                ->first();
        }
        return $this->customer->discounts()->where('status', Meta::UNUSED_DISCOUNT)->first();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function createSelfServiceOrder()
    {
        $orderServices = $this->collectOrderServices();
        $paymentMethod = $this->getPaymentMethod();
        $orderStatus = (in_array($paymentMethod, [PaymentMethod::CASH_PAYMENT, PaymentMethod::POS_PAYMENT])) ? Meta::ORDER_STATUS_COMPLETED : Meta::ORDER_STATUS_PENDING;
        $orderAmount = array_sum(Arr::pluck($orderServices, 'total'));
        DB::beginTransaction();
        try {
            $amountBeforeDiscount = $orderAmount;
            //Check and apply discount if card payment is specified
            $userUnusedDiscount = $this->getUserDiscount();
            if(!PaymentMethod::methodIsPosOrCash($paymentMethod) && !empty($userUnusedDiscount)) {
                $orderAmount -= $userUnusedDiscount->discount_earned;
            }

            //Create the order
            $this->order = Order::create([
                'user_id' => $this->customer->id,
                'order_type' => Meta::SELF_SERVICE_ORDER_TYPE,
                'status' => $orderStatus,
                'amount' => ($orderAmount <= 0) ? 0: $orderAmount,
                'amount_before_discount' => $amountBeforeDiscount,
                'payment_method' => $paymentMethod,
                'created_by' => $this->authUser->id,
                'location_id' => $this->requestParams['location'],
                'company_id' => $this->requestParams['company'],
            ]);
            $this->order->services()->attach($orderServices);

            //Add an entry to the orders_discounts table if applicable
            if(!PaymentMethod::methodIsPosOrCash($paymentMethod)) {
                if (!empty($userUnusedDiscount)) {
                    OrdersDiscount::create([
                        'order_id' => $this->order->id,
                        'users_discount_id' => $userUnusedDiscount->id,
                        'loyalty_offer_id' => $userUnusedDiscount->offer_id
                    ]);
                    $userUnusedDiscount->update(['status' => Meta::DISCOUNT_APPLIED_TO_ORDER]);
                }else{
                    $discountApplied = $this->createUserDiscountOrFail();
                    if($discountApplied){
                        $this->debugLog("Discount earned by user, ORDER_ID:{$this->order->id}");
                    }
                }
            }else{
                Transaction::create([
                    'reference_code' => generateUniqueRef('EZWV2-OR', $this->order->id),
                    'order_id' => $this->order->id,
                    'user_id' => $this->customer->id,
                    'transaction_type_id' => TransactionType::ORDER_PAYMENT_ID,
                    'transaction_status_id' => TransactionStatus::COMPLETED,
                    'amount' => ($orderAmount <= 0) ? 0: $orderAmount,
                    'transaction_payment_method_id' => $paymentMethod
                ]);
            }
            DB::commit();
            $responseMessage = ($orderStatus == Meta::ORDER_STATUS_COMPLETED)?
                'Order created successfully':
                'Order created successfully, a notification has been sent to the user to initiate payment'
            ;
            return [
                'status' => true,
                'message' => $responseMessage
            ];

        } catch (\Exception $e){
            DB::rollback();
            Log::error('Unable to create selfservice order :' . $e->getTraceAsString());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function updateOrder()
    {
        $orderServices = $this->collectOrderServices();
        $paymentMethod = $this->getPaymentMethod();
        $orderStatus = (in_array($paymentMethod, [PaymentMethod::CASH_PAYMENT, PaymentMethod::POS_PAYMENT])) ? Meta::ORDER_STATUS_COMPLETED : Meta::ORDER_STATUS_PENDING;
        $orderAmount = array_sum(Arr::pluck($orderServices, 'total'));
        $amountBeforeDiscount = $orderAmount;
        $oldStatus = $this->order->status;
        DB::beginTransaction();
        try {
            //Get user discount if exists
            $discountToApply = $this->getUserDiscount();

            //Get order discount if already applied
            if($this->order->discount()->exists()){
                $appliedUserDiscount = $this->order->discount->users_discount;
                if(!PaymentMethod::methodIsPosOrCash($paymentMethod)) {
                    $discountToApply = $appliedUserDiscount;
                }else{
                    $this->order->discount()->delete();
                    $appliedUserDiscount->update([
                        'status' => Meta::UNUSED_DISCOUNT
                    ]);
                }
            }

            if(!empty($discountToApply)){
                $orderAmount -= $discountToApply->discount_earned;
            }

            // Update the order
            $this->order->update([
                'status' => $orderStatus,
                'amount' => ($orderAmount <= 0) ? 0: $orderAmount,
                'amount_before_discount' => $amountBeforeDiscount,
                'payment_method' => $paymentMethod,
            ]);
            $this->order->services()->sync($orderServices);
            if($amountBeforeDiscount > $orderAmount){
                OrdersDiscount::updateOrCreate([
                    'order_id' => $this->order->id,
                ],[
                    'users_discount_id' => $discountToApply->id,
                    'loyalty_offer_id' => $discountToApply->offer_id
                ]);
                $discountToApply->update([
                    'status' => Meta::DISCOUNT_APPLIED_TO_ORDER
                ]);
            }else if(!PaymentMethod::methodIsPosOrCash($paymentMethod)){
                $discountApplied = $this->createUserDiscountOrFail();
                if($discountApplied){
                    $this->debugLog("Discount earned by user, ORDER_ID:{$this->order->id}");
                }
            }

            if(in_array($paymentMethod, [PaymentMethod::CASH_PAYMENT, PaymentMethod::POS_PAYMENT])){
                Transaction::create([
                    'reference_code' => generateUniqueRef('EZWV2-OR', $this->order->id),
                    'order_id' => $this->order->id,
                    'user_id' => $this->customer->id,
                    'transaction_type_id' => TransactionType::ORDER_PAYMENT_ID,
                    'transaction_status_id' => TransactionStatus::COMPLETED,
                    'amount' => ($orderAmount <= 0) ? 0: $orderAmount,
                    'transaction_payment_method_id' => $paymentMethod
                ]);
            }

            if($this->order->order_type === Meta::DROP_OFF_ORDER_TYPE && array_key_exists('lockers', $this->requestParams)){
                $selectedLockers = $this->requestParams['lockers'];
                $previouslySelected = $this->order->lockers();
                if($previouslySelected->count() > 0){
                    $previouslySelected->update([
                        'occupied' => false
                    ]);
                }
                $lockers = $this->order->location->lockers()->whereIn('locker_number', $selectedLockers);
                $this->order->lockers()->sync($lockers->get());
                $lockers->update([
                    'occupied' => 1
                ]);
            }
            if($orderStatus === Meta::ORDER_STATUS_COMPLETED){
                $result = $this->processDeliveryRequest();
                if($oldStatus == Meta::ORDER_STATUS_PENDING) {
                    $this->order->update(['completed_at' => now()]);
                }
                $this->order->emptyRelatedLockers();
            }

            if($this->order->order_type === Meta::DROP_OFF_ORDER_TYPE && !PaymentMethod::methodIsPosOrCash($paymentMethod) && $this->order->order_services()->count() > 0) {
                $this->customer->notify(new ReadyForPickupNotification($this->order));
            }

            DB::commit();
            $responseMessage = ($orderStatus == Meta::ORDER_STATUS_COMPLETED)?
                'Order created successfully':
                'Order created successfully, a notification has been sent to the user to initiate payment'
            ;
            return [
                'status' => true,
                'message' => $responseMessage
            ];

        } catch (KwikRequestException $e) {
            DB::rollback();
            $e->report();
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e){
            DB::rollback();
            Log::error('Unable to update selfservice order :' . $e->getTraceAsString());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function orderPaidWithCard(){
        if($this->order->status === ORDER_STATUS_COMPLETED){
            return [
                'status' => true,
                'message' => 'Order already marked as completed'
            ];
        }

        DB::beginTransaction();
        try {
            $discountApplied = ($this->order->discount()->exists())? $this->order->discount->users_discount: null;

            //Update the order
            $this->order->update([
                'status' => ORDER_STATUS_COMPLETED,
                'completed_at' => now()
            ]);
            $this->order->emptyRelatedLockers();

            if($discountApplied){
                $discountApplied->update([
                    'status' => Meta::USED_DISCOUNT
                ]);
            }
            $discountApplied = $this->createUserDiscountOrFail();
            if($discountApplied){
                $this->debugLog("Discount earned by user, ORDER_ID:{$this->order->id}");
            }

            $result = $this->processDeliveryRequest();

            $responseMessage = 'Order updated successfully';
            DB::commit();
            return [
                'status' => true,
                'message' => $responseMessage
            ];

        } catch (KwikRequestException $e) {
            DB::rollback();
            logCriticalError("OrderID-{$this->order->id} MSG: OrderKwikRequestException: . {$e->getMessage()}",  $e);
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ];
        } catch (\Exception $e){
            DB::rollback();
            Log::error('Unable to update order after card payment :' . $e->getTraceAsString());
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Checks and registers user discounts if a the user is eligible.
     *
     * @return bool
     */
    public function createUserDiscountOrFail()
    {
        //get the currently active offer and fail if none exists
        $activeOffer = LoyaltyOffer::where('company_id', $this->order->company_id)
            ->where('start_date', '<', now())
            ->where('end_date', '>', now())
            ->where('is_special_offer', false)
            ->where('status', true)
            ->first();
        if(empty($activeOffer)) {
            $this->debugLog("No active Loyalty Offers");
            return false;
        }

        //Get the last time user has enjoyed a discount within this offer period
        $latestOrderWithDiscount = $this->customer->orders()->where('payment_method', PaymentMethod::CARD_PAYMENT)
            ->where('created_at', '>', $activeOffer->start_date)
            ->whereHas('discount')->latest()->first();
        $eligibilityStartDate = (!empty($latestOrderWithDiscount)) ? $latestOrderWithDiscount->created_at : $activeOffer->start_date;

        //Get the amount spent since last time discount was enjoyed or offer started
        $amountSpentSinceEligible = $this->customer->orders()->where('status', Meta::ORDER_STATUS_COMPLETED)
            ->where('payment_method', PaymentMethod::CARD_PAYMENT)
            ->where('created_at', '>', $eligibilityStartDate)
            ->sum('amount');
        if($amountSpentSinceEligible >= $activeOffer->spending_requirement)
        {
            $discountEarned = $activeOffer->discount_value * floor($amountSpentSinceEligible/$activeOffer->spending_requirement);
            $this->customer->discounts()->create([
                'offer_id' => $activeOffer->id,
                'amount_spent' => $amountSpentSinceEligible,
                'discount_earned' => $discountEarned,
                'status' => Meta::UNUSED_DISCOUNT
            ]);
            $this->debugLog("Discount($discountEarned) applied to the user");
            return true;
        }
        $this->debugLog('Amount spent => '. $amountSpentSinceEligible);
        $this->debugLog('spending_requirement => '. $activeOffer->spending_requirement);
        $this->debugLog('User did not meet the spending requirement.');
        return false;
    }

    /**
     * @return array|bool
     * @throws KwikRequestException
     */
    public function processDeliveryRequest()
    {
        if(!$this->order->hasDeliveryRequest(true)){
            $this->debugLog('No delivery task attached');
            return true;
        }
        $inst = (new PaymentsController);
        $inst->setOrLogWebhookDebugSession(['has_delivery_request' => true]);

        $deliveryRequest = $this->order->deliveryRequest();

        $jobDetails = json_decode($deliveryRequest->job_details_payload, true);
        $pickupTaskTime = Carbon::parse($jobDetails['pickup']['time']);

        $inst->setOrLogWebhookDebugSession(['delivery_job_details' => $jobDetails]);
        if($pickupTaskTime->isBefore(Carbon::parse('2021-08-18'))){
            $deliveryRequest->update(['order_request_status_id' => OrderRequestStatus::DELIVERY_MANUALLY_SORTED]);
            $inst->setOrLogWebhookDebugSession(['delivery_expired' => true]);
            return [
                'status' => false,
                'message' => 'Delivery request expired'
            ];
        }
        $inst->setOrLogWebhookDebugSession(['delivery_expired' => false]);
        $currentTime = now();
        if($pickupTaskTime->isPast()){
            $oldJobDetails = $jobDetails;
            $earliestTimeToday = (clone $currentTime)->setTime(9,0);
            $latestTimeToday = (clone $currentTime)->setTime(17,0);
            if($currentTime->isWeekend()){
                $nextWeekDay = $currentTime->nextWeekday();
                $jobDetails['pickup']['time'] = (clone $nextWeekDay)->setTime(9,0)->toDateTimeString();
                $jobDetails['delivery']['time'] = (clone $nextWeekDay)->setTime(9,40)->toDateTimeString();
            } elseif ($currentTime->isBefore($earliestTimeToday)){
                $jobDetails['pickup']['time'] = (clone $earliestTimeToday)->toDateTimeString();
                $jobDetails['delivery']['time'] = (clone $earliestTimeToday)->addMinutes(40)->toDateTimeString();
            } elseif ($currentTime->isAfter($latestTimeToday)){
                $jobDetails['pickup']['time'] = (clone $earliestTimeToday)->addDay()->toDateTimeString();
                $jobDetails['delivery']['time'] = (clone $earliestTimeToday)->addDay()->addMinutes(40)->toDateTimeString();
            } else {
                $jobDetails['pickup']['time'] = $currentTime->addMinutes(10)->toDateTimeString();
                $jobDetails['delivery']['time'] = $currentTime->addMinutes(40)->toDateTimeString();
            }
            $this->debugLog( $currentTime->toDateTimeString() . " is past the delivery request time . \n" . json_encode([
                'old_pickup_time' => $oldJobDetails['pickup']['time'],
                'new_pickup_time' => $jobDetails['pickup']['time'],
                'old_delivery_time' => $oldJobDetails['delivery']['time'],
                'new_delivery_time' =>  $jobDetails['delivery']['time']
            ]));
        }
        $pickupReqHandler = new KwikRequestsHandler();
        $response = $pickupReqHandler->createTask($jobDetails, $deliveryRequest->temp_order_request);

        $inst->setOrLogWebhookDebugSession(['delivery_request_result' => $response]);
        if($response['status'] === false){
            $message = 'Delivery request processing failed';
            $this->debugLog($message . ' - ' . json_encode($response));
            throw new KwikRequestException($message);
        }

        $deliveryRequest->update(['job_details_payload' => json_encode($jobDetails)]);

        $responseData = $response['data'];
        if(isset($responseData['pickups']) && !empty($responseData['pickups'])) {
            $jobPickup = $responseData['pickups'][0];
            $jobDelivery = $responseData['deliveries'][0];
            $orderRequest = $this->order->deliveryRequest();
            $orderRequest->update([
                'order_request_status_id' => OrderRequestStatus::DELIVERY_REQUESTED,
                'kwik_job_ids' => $jobPickup['job_id'] . ',' . $jobDelivery['job_id'],
                'kwik_order_id' => (isset($responseData['unique_order_id']) && !empty($responseData['unique_order_id']))
                    ? $responseData['unique_order_id']: null,
                'has_pickup' => $this->order->hasPickupRequest(),
            ]);
            return [
                'status' => true,
                'data' => $responseData
            ];
        }
        $errorMessage = 'Malformed kwik task response';
        $this->debugLog($errorMessage. ' - '. json_encode($responseData));
        throw new KwikRequestException($errorMessage);
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    public function debugLog($message)
    {
        logger("OrderID-". $this->order->id ." MSG: $message");
    }
}
