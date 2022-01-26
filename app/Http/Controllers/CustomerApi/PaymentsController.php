<?php

namespace App\Http\Controllers\CustomerApi;

use App\Classes\OrderRequestHandler;
use App\Classes\PaystackRequestHandler;
use App\Http\Resources\UserCardResource;
use App\Jobs\ProcessUserNotification;
use App\Jobs\RefundCardConfirmationCost;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Models\UserCard;
use App\Rules\OrderValidForPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentsController extends Controller
{
    protected $guard = 'users';
    protected $user;
    protected $paystackRequestHandler;

    /**
     * PaymentsController constructor.
     */
    public function __construct()
    {
        $this->paystackRequestHandler = new PaystackRequestHandler();
        $this->user = $this->getAuthUser($this->guard);
    }

    /**
     * Allows users add a new card by paying N50 to
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCard(Request $request)
    {
        try{
            $amount = (int)(config('paystack.charges') * 100); // Amount to charge in Kobo
            $reference = generateUniqueRef('EZWV2-CA', null);
            Transaction::create([
                'transaction_type_id' => TransactionType::NEW_CARD_ID,
                'user_id' => $this->user->id,
                'amount' => $amount/100,
                'reference_code' => $reference,
                'transaction_status_id' => TransactionStatus::PENDING
            ]);
            $data = [
                'email' => $this->user->email,
                'amount' => $amount,
                'callback_url' => route('add_card.verify'),
                'reference' => $reference,
                'metadata' => [
                    "custom_fields" => [
                        ["display_name" => "User ID", "variable_name" => "user_id", "value" => $this->user->id],
                        [
                            "display_name" => "Purpose",
                            "variable_name" => "purpose",
                            "value" => TransactionType::NEW_CARD
                        ]
                    ]
                ],
                'channels' => ['card']
            ];
            $responseData = $this->paystackRequestHandler->initializeTransaction($data);
            if(!$responseData['status']){
                return errorResponse('Unable to process the request at the moment', 500, $request);
            }
            $response_data = [
                'url' => $responseData['data']['authorization_url']
            ];
            $response_message = "Success";
            return successResponse($response_message, $response_data, $request);

        }catch(\Exception $e)
        {
            return errorResponse('Something went wrong', 500, $request, $e);
        }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allCards(Request $request)
    {
        try{
            $user_cards =  UserCardResource::collection($this->user->user_cards);
            $responseData = $user_cards->toArray($request);
            $responseMessage = "Found {$user_cards->count()} cards";

            if($user_cards->count() === 0){
                $responseMessage = "Customer don't have any card registered";
            }
            return successResponse($responseMessage, $responseData, $request);
        }catch(\Exception $e)
        {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function deleteCard(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'card_id' => ['required','exists:user_cards,id']
            ]);

            if ($validator->fails())
            {
                throw new ValidationException($validator);
            }
            if($this->user->user_cards()->count() > 1)
            {
                $data = [
                    'authorization_code' => $this->user->user_cards()->where('id', $request->card_id)->first()->auth_code
                ];
                $responsePayload = $this->paystackRequestHandler->deactivateAuthorization($data);
                if($responsePayload['status'])
                {
                    $this->user->user_cards()->where('id', $request->card_id)->delete();
                    return successResponse('Card has been deleted successfully', Null, $request);
                }
                return errorResponse('Something went wrong, please try again later.', 400, $request);

            }else{

                return errorResponse('You cannot delete this card at the moment. Add more cards to delete this one.', 400, $request);


            }

        }catch (ValidationException $e)
        {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array) $errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);
        }catch(\Exception $e)
        {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payWithNewCard(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'order_id' => ['required', 'exists:orders,id', new OrderValidForPayment($this->user)],
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $order = Order::find($request->order_id);
            $amount = $order->getAmountToPay();
            $reference = generateUniqueRef('EZWV2-OR', $order->id);
            Transaction::create([
                'transaction_type_id' => TransactionType::ORDER_PAYMENT_ID,
                'user_id' => $this->user->id,
                'amount' => $amount,
                'reference_code' => $reference,
                'transaction_status_id' => TransactionStatus::PENDING,
                'order_id' => $order->id
            ]);
            $data = [
                'email' => $this->user->email,
                'amount' => ($amount * 100),   //The $amount is in Nigeria Kobo, add double zeros to it
                'callback_url' => route('order_payment.verify_new_card'),
                'reference' => $reference,
                'metadata' => [
                    "custom_fields" => [
                        ["display_name" => "User ID", "variable_name" => "user_id", "value" => $this->user->id],
                        ["display_name" => "Order ID", "variable_name" => "order_id", "value" => $order->id],
                        ["display_name" => "Purpose", "variable_name" => "purpose", "value" => TransactionType::ORDER_PAYMENT]
                    ]
                ],
                'channels' => ['card']
            ];

            $responseData = $this->paystackRequestHandler->initializeTransaction($data);
            if(!$responseData['status']){
                return errorResponse('Unable to process the request at the moment', 500, $request);
            }
            $response_data = [
                'url' => $responseData['data']['authorization_url']
            ];
            $response_message = "Success";
            return successResponse($response_message, $response_data, $request);

        }catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        }catch(\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function payWithExistingCard(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'order_id' => ['required', 'exists:orders,id', new OrderValidForPayment($this->user)],
                'card_id' => ['required', 'exists:user_cards,id', function($attribute, $value, $fail){
                    if(!User::find($this->user->id)->user_cards()->where('id', $value)->exists()){
                        $fail("Invalid $attribute selected");
                    }
                }]
            ]);
            if ($validator->fails())
            {
                throw new ValidationException($validator);
            }
            $card = UserCard::find($request->card_id);
            $order = Order::find($request->order_id);
            $order_link = url('order/'.$order->id);
            $amount = $order->getAmountToPay();
            $data = [
                'authorization_code' => $card->auth_code,
                'email' => $this->user->email,
                'amount' => ($amount * 100),  //The $totalAmount is in Nigeria Kobo, add double zeros to it
                'reference' => generateUniqueRef('EZWV2-OR', $order->id),
                'metadata' => [
                    "custom_fields" => [
                        ["display_name" => "Order ID", "variable_name" => "order_id", "value" => $order->id],
                        ["display_name" => "Purpose", "variable_name" => "purpose", "value" => TransactionType::ORDER_PAYMENT]
                    ]
                ],
            ];
            $transaction = Transaction::firstOrCreate([
                'reference_code' => $data['reference'],
            ],[
                'transaction_type_id' => TransactionType::ORDER_PAYMENT_ID,
                'order_id' => $request->order_id,
                'card_id' => $card->id,
                'user_id' => $this->user->id,
                'amount' => $amount,
                'metadata' => json_encode(['request' => $data]),
                'transaction_status_id' => TransactionStatus::PENDING,
            ]);
            $responsePayload = $this->paystackRequestHandler->paymentWithExistingCard($data);
            if(!isset($responsePayload['data'])){
                if(isset($responsePayload['notify_user'])) {
                    return successResponse($responsePayload['message'], null, $request, false);
                }
                return errorResponse('Unable to process payment', 400, $request);
            }
            else {
                $responseData = $responsePayload['data'];
                $transaction_amount = ($responseData['amount'] / 100);
                if ($order->getAmountToPay() > $transaction_amount) {
                    $responseMessage = "You paid less than the order amount. Please contact EzWash support";
                    $status = false;
                }else {
                    $responseMessage = "Your payment is being processed.";
                    $status = true;
                }
                return successResponse($responseMessage, null, $request, $status);
            }

        }catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            return errorResponse($message, 400, $request);

        }catch(\Exception $e) {
            return errorResponse('Something went wrong', 500, $request, $e);
        }
    }

    public function webhookHandler(Request $request)
    {
        try {
            if (!array_key_exists('HTTP_X_PAYSTACK_SIGNATURE', $_SERVER)) {
                return errorResponse('Missing Signature Header', 400, $request);
            }

            $paystackSecret = config("paystack.paystack_secret_key");
            $input = @file_get_contents("php://input");

            if ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $paystackSecret)) {
                if (array_key_exists('HTTP_X_PAYSTACK_WEBHOOK_MANUAL', $_SERVER) && $_SERVER['HTTP_X_PAYSTACK_WEBHOOK_MANUAL'] !== $paystackSecret){
                    return errorResponse('Invalid Signature Header', 400, $request);
                }
            }
            $eventResponse = $request->input();

            $validator = Validator::make($eventResponse, [
                'event' => ['required','in:charge.success'],
                'data' => ['required'],
                'data.channel' => ['required','in:card'],
                'data.reference' => ['required', 'exists:transactions,reference_code']
            ], [
                'data.channel.in' => "Payment channel is ':input' and not 'card'",
                'data.reference.exists' => 'No transaction associated with :input'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $eventData = $eventResponse['data'];
            $reference = $eventData['reference'];

            $this->setOrLogWebhookDebugSession(['reference' => $reference, 'webhook_request_input' => $input]);
            $transaction = Transaction::where('reference_code', $reference)->first();

            $responsePayload = $this->paystackRequestHandler->verifyTransaction($reference);
            $responsePayloadStatus = $responsePayload['status'] ?? false;
            if(!$responsePayloadStatus){
                throw new \Exception('Unable to verify transaction');
            }

            $responseData = $responsePayload['data'];
            $handlerType = null;
            if($transaction->transaction_type_id === TransactionType::ORDER_PAYMENT_ID){
                $result = $this->handleOrderPayment($responseData, $transaction);
                $handlerType = 'handleOrderPayment';
            } else {
                $result = $this->handleCardAddition($responseData, $transaction);
                $handlerType = 'handleCardAddition';
            }
            if(!$result){
                $this->setOrLogWebhookDebugSession([
                    'status' => false,
                    'message' => "'{$handlerType}' processing failed"
                ], true, true);
                return errorResponse('Webhook processing failed', 400, $request);
            }

            $this->setOrLogWebhookDebugSession(['status' => $responsePayloadStatus, 'message' => 'Paystack verification response:: ' . $responsePayload['message']], true, $responsePayloadStatus);
            return successResponse('Successful', null, $request);

        } catch (ValidationException $e) {
            $errors = flattenArray(array_values(
                (array)$e->errors()
            ));
            $message = implode(", ", $errors);

            if(array_key_exists('data.channel', $e->errors()) || array_key_exists('event', $e->errors())){
                return successResponse($message, null);
            }
            return errorResponse($message, 400, $request);

        } catch (\Exception $e) {
            $msg = 'Something went wrong';
            $this->setOrLogWebhookDebugSession(['status' => false, 'message' => $e->getMessage()], true, true);
            return errorResponse($msg, 500, $request, $e);
        }
    }

    /**
     * @param $paystackResultData
     * @param Transaction|Model $transaction
     * @return bool
     * @throws \Exception
     */
    function handleOrderPayment($paystackResultData, $transaction)
    {
        $order = $transaction->order;
        $thisUser = $transaction->user;
        $employee = $order->order_creator;
        $transaction_amount = ($paystackResultData['amount'] / 100);
        $authorization = (isset($paystackResultData['authorization']) && !empty($paystackResultData['authorization'])) ? $paystackResultData['authorization'] : null;
        $order_url = route('order.view', ['order' => $order->id]);

        if ($paystackResultData['status'] == 'success' && $order->status === ORDER_STATUS_COMPLETED) {
            $transaction->update([
                'transaction_status_id' => TransactionStatus::COMPLETED,
                'amount' => $transaction_amount,
            ]);
            $this->setOrLogWebhookDebugSession(['order_already_resolved' => "Order already marked as completed"]);
            return true;
        } else {
            if($authorization) {
                if(isset($authorization['signature']) && !empty($authorization['signature'])){
                    $card = UserCard::firstOrCreate([
                        'user_id' => $thisUser->id,
                        'signature' => $authorization['signature']
                    ]);
                } else {
                    $card = UserCard::firstOrCreate([
                        'user_id' => $thisUser->id,
                        'auth_code' => $authorization['authorization_code'],
                        'signature' => '-'
                    ]);
                }

                $card->update([
                    'auth_code' => $authorization['authorization_code'],
                    'last_four' => $authorization['last4'],
                    'card_type' => $authorization['card_type'],
                    'exp_month' => $authorization['exp_month'],
                    'exp_year' => $authorization['exp_year'],
                    'bank' => $authorization['bank'],
                    'meta' => json_encode($authorization, true)
                ]);
            }
            $meta = json_decode($transaction->metadata, true);
            $meta['response'] = $paystackResultData;
            $transaction->update([
                'card_id' => (isset($card)) ? $card->id : $transaction->card_id,
                'transaction_status_id' => $paystackResultData['status'] == 'failed' ? TransactionStatus::FAILED : TransactionStatus::COMPLETED,
                'amount' => $transaction_amount,
                'metadata' => json_encode($meta),
                'message' => $paystackResultData['gateway_response'],
                'webhook_confirmed' => true
            ]);
            if ($order->getAmountToPay() <= $transaction_amount) {
                $orderHandler = new OrderRequestHandler([], $order->order_creator, $order);
                $updateOrderPayment = $orderHandler->orderPaidWithCard();
                if(!$updateOrderPayment['status']){
                    $this->setOrLogWebhookDebugSession([
                        'orderPaidWithCard_result' => $updateOrderPayment
                    ]);
                    return false;
                }
                $data = [
                    'message' => "Your payment of {$transaction_amount} was received successfully",
                    'notification_type' => "payment_response"
                ];
                Queue::push(new ProcessUserNotification(
                    $thisUser,
                    '\App\Notifications\CompletedPaymentNotification',
                    [$data]
                ));
                $notification = [
                    'employee_id' => $employee->id,
                    'status' => Notification::UNREAD,
                    'url' => $order_url,
                    'heading' => 'Completed Order Payment',
                    'message' => $thisUser->name . ' just paid N' . $transaction->amount . ' for an order.',
                    'tag' => 'order_payment'
                ];
                Notification::create($notification);
                return true;
            } else {
                $notification = [
                    'employee_id' => $employee->id,
                    'status' => Notification::UNREAD,
                    'url' => $order_url,
                    'heading' => 'Incomplete payment',
                    'message' => $thisUser->name . ' just paid N' . $transaction->amount . ' which is less then the expected amount - N' . number_format($order->getAmountToPay(), 2) .'.',
                    'tag' => 'order_payment'
                ];
                $this->setOrLogWebhookDebugSession(['invalid_amount' => "Amount paid(N{$transaction_amount}) is less than order amount N{$order->getAmountToPay()}"]);
                Notification::create($notification);
                return false;
            }
        }
    }

    /**
     * @param $paystackResultData
     * @param Transaction|Model $transaction
     * @return bool
     */
    function handleCardAddition($paystackResultData, $transaction)
    {
        $thisUser = $transaction->user;
        $transaction_amount = ($paystackResultData['amount'] / 100);
        $authorization = (isset($paystackResultData['authorization']) && !empty($paystackResultData['authorization'])) ? $paystackResultData['authorization'] : null;
        if ($authorization){
            $card = UserCard::firstOrCreate([
                'user_id' => $thisUser->id,
                'signature' => $authorization['signature']
            ]);
            $card->update([
                'auth_code' => $authorization['authorization_code'],
                'last_four' => $authorization['last4'],
                'card_type' => $authorization['card_type'],
                'exp_month' => $authorization['exp_month'],
                'exp_year' => $authorization['exp_year'],
                'bank' => $authorization['bank'],
                'meta' => json_encode($authorization, true)
            ]);
            $meta = json_decode($transaction->metadata, true);
            $meta['response'] = $paystackResultData;
            $transaction->update([
                'card_id' => $card->id,
                'transaction_status_id' => $paystackResultData['status'] == 'failed' ? TransactionStatus::FAILED : TransactionStatus::COMPLETED,
                'amount' => $transaction_amount,
                'metadata' => json_encode($meta),
                'message' => $paystackResultData['gateway_response'],
                'webhook_confirmed' => true
            ]);
            $data = [
                'message' => "Your payment of {$transaction_amount} was received successfully",
                'notification_type' => "payment_response"
            ];
            $cardAddedData = [
                'subject' => "New Card Added",
                'message' => "You have successfully added your card.",
                'notification_type' => "card_added"
            ];

            Queue::push(new ProcessUserNotification(
                $thisUser,
                '\App\Notifications\CompletedPaymentNotification',
                [$data]
            ));
            Queue::push(new ProcessUserNotification(
                $thisUser,
                '\App\Notifications\CompletedPaymentNotification',
                [$cardAddedData]
            ));
            Queue::push(new RefundCardConfirmationCost($transaction));
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @param array $data
     * @param bool $logData
     * @param bool $error
     */
    function setOrLogWebhookDebugSession($data, $logData = false, $error = false)
    {
        $sessionData = Session::get('WEBHOOK_DEBUG_SESSION_KEY', []);
        Session::put('WEBHOOK_DEBUG_SESSION_KEY', array_merge($sessionData, $data));
        if($logData){
            $reference = $sessionData['reference'] ?? '#';
            $debugMessage = "Webhook Request Debugger:: {$reference}\n" . json_encode($sessionData);
            if($error){
                Log::error($debugMessage);
            } else {
                Log::info($debugMessage);
            }
        }
    }
}
