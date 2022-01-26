<?php

namespace App\Http\Controllers;

use App\Classes\OrderRequestHandler;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Classes\PaystackRequestHandler;

class PaymentController extends Controller
{

    protected $paystackRequestHandler;

    public function __construct()
    {
        $this->paystackRequestHandler = new PaystackRequestHandler();
    }

    public function verifyCard(Request $request)
    {
        $salutation = 'Hello';
        try {
            $validator = Validator::make($request->all(), [
                'reference' => 'required|exists:transactions,reference_code'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $responseData = $this->paystackRequestHandler->verifyTransaction($request->reference);

            $transaction = Transaction::where('reference_code', $request->reference)->first();
            $user = $transaction->user;
            $salutation .= " $user->name";
            if (isset($responseData['data'])) {
                $response_data = $responseData['data'];
                if ($response_data['status'] == 'success' && !empty($response_data['authorization'])) {
                    $transaction->update([
                       'transaction_status_id' => TransactionStatus::COMPLETED,
                       'metadata' => json_encode($response_data['metadata'], true),
                       'header' => json_encode($response_data, true),
                       'message' => $response_data['gateway_response']
                    ]);
                    $response_data_auth = $response_data['authorization'];
                    $transaction = Transaction::where('reference_code', $response_data['reference'])->first();
                    UserCard::updateOrCreate([
                        'signature' => $response_data_auth['signature']
                    ], [
                        'user_id' => $transaction->user_id,
                        'auth_code' => $response_data_auth['authorization_code'],
                        'last_four' => $response_data_auth['last4'],
                        'card_type' => $response_data_auth['card_type'],
                        'exp_month' => $response_data_auth['exp_month'],
                        'exp_year' => $response_data_auth['exp_year'],
                        'bank' => $response_data_auth['bank'],
                        'meta' => json_encode($response_data_auth, true)
                    ]);
                    $responseMessage = "You have successfully added your card.";
//                    $data = [
//                        'subject' => "New Card Added",
//                        'message' => $message,
//                        'notification_type' => "card_added"
//                    ];
//                    Queue::push(new ProcessUserNotification(
//                        User::find($user->id),
//                        new CompletedPaymentNotification($data)
//                    ));
                } else {
                    $transaction->update([
                        'transaction_status_id' => ($response_data['status'] == 'reversed') ? TransactionStatus::REFUNDED : TransactionStatus::FAILED,
                        'message' => $response_data['gateway_response']
                    ]);
                    $responseMessage = $response_data['gateway_response'];
                }
            } else {
                $responseMessage = $responseData['message'];
            }

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $responseMessage = $errors[0];
        } catch (\Exception $e) {
            logCriticalError('Something went wrong', $e);
            $responseMessage = "Something went wrong";
        }
        return view('general.guest-info', compact('salutation', 'responseMessage'));
    }

    public function verifyOrderPayment(Request $request)
    {
        $salutation = "Oops!!";
        $responseMessage = "Unable to process payment";
        try {
            $validator = Validator::make($request->all(), [
                'reference' => 'required|exists:transactions,reference_code'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $responsePayload = $this->paystackRequestHandler->verifyTransaction($request->reference);
            if (isset($responsePayload['status']) && $responsePayload['status'] === true) {
                $responseData = $responsePayload['data'];
                $transaction = Transaction::where('reference_code', $responseData['reference'])->first();
                $order = $transaction->order;
                $user_id = $transaction->user_id;
                $transaction_amount = ($responseData['amount'] / 100);
                $authorization = (isset($responseData['authorization']) && !empty($responseData['authorization'])) ? $responseData['authorization'] : null;
                $user = User::find($user_id);
                if ($order->status === ORDER_STATUS_COMPLETED) {
                    $salutation = "Thank you, " . $user->firstName();
                    $responseMessage = "This order has already been processed";
                } else {
                    $card = UserCard::firstOrCreate([
                        'user_id' => $user_id,
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
                    $transaction->update([
                        'card_id' => $card->id,
                        'transaction_status_id' => $responseData['status'] == 'failed' ? TransactionStatus::FAILED : TransactionStatus::COMPLETED,
                        'amount' => $transaction_amount,
                        'metadata' => json_encode($responseData['metadata'], true),
                        'header' => json_encode($responseData, true),
                        'message' => $responseData['gateway_response']
                    ]);
                    if ($order->getAmountToPay() <= $transaction_amount) {
                        $orderHandler = new OrderRequestHandler([], null, $order);
                        $updateOrderPayment = $orderHandler->orderPaidWithCard();

                        $salutation = "Thank you, " . $user->firstName();
                        $responseMessage = "Your payment is being processed...";
//                        $data = [
//                            'user' => $user,
//                            'message' => $responseMessage,
//                            'notification_type' => "payment_response"
//                        ];
//                        Queue::push(new ProcessUserNotification(
//                            User::find($user->id),
//                            new CompletedPaymentNotification($data)
//                        ));
//                        $employee = $order->order_creator;
//                        $notification = [
//                            'employee_id' => $employee->id,
//                            'status' => Notification::UNREAD,
//                            'url' => $order_url,
//                            'heading' => 'Completed Order Payment',
//                            'message' => $user->name . ' just paid N' . $transaction->amount . ' for an order.',
//                            'tag' => 'order_payment'
//                        ];
//                        Notification::create($notification);
                    } else {
                        $responseMessage = "<span>You paid less than the order amount.</span>";
                        $responseMessage .= "<span>Please contact an EzWash employee to resolve this issue,</span>";
                    }
                }
            }

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $responseMessage = $errors[0];

        } catch(\Exception $e) {
            $responseMessage = "An error occurred while processing your payment";
            logCriticalError('Order payment verification error', $e);
        }
        return view('general.guest-info', compact('salutation', 'responseMessage'));
    }
}
