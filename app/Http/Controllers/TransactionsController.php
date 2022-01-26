<?php

namespace App\Http\Controllers;


use App\Classes\Meta;
use App\Classes\PaystackRequestHandler;
use App\Http\Controllers\CustomerApi\PaymentsController;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Notifications\CompletedPaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TransactionsController extends Controller
{

    protected $paystackRequestHandler;
    public function __construct()
    {
        $this->paystackRequestHandler = new PaystackRequestHandler();
        $this->middleware('permission:list_transactions', ['only' => ['index']]);
        $this->middleware('permission:confirm_transaction', ['only' => ['confirmStatus']]);
    }

    public function index(Request $request)
    {
        $allTransactions = $this->getFilteredTransactions($request, 20);
        $authUser = $this->getAuthUser();
        $transaction_statuses = TransactionStatus::all();
        $paymentMethods = PaymentMethod::all();
        return view('transaction.index', compact('authUser', 'transaction_statuses', 'paymentMethods', 'allTransactions'));
    }

    public function confirmStatus(Request $request)
    {
        try {
            $this->validate($request, [
                'transaction' => 'required|numeric|exists:transactions,id'
            ]);
            $requestResponse = (object)[
                'status' => false,
                'message' => 'Failed'
            ];
            $transaction = Transaction::where('id', $request->transaction)->first();
            if ($transaction->transaction_payment_method_id === PaymentMethod::CARD_PAYMENT) {
                $transaction_reference = $transaction->reference_code;
                $responsePayload = $this->paystackRequestHandler->verifyTransaction($transaction_reference);
                if (isset($responsePayload['status']) && $responsePayload['status'] === true) {
                    $responseData = $responsePayload['data'];
                    if ($transaction->transaction_type_id === TransactionType::ORDER_PAYMENT_ID) {
                        $result = (new PaymentsController())->handleOrderPayment($responseData, $transaction);
                    } else {
                        $result = (new PaymentsController())->handleCardAddition($responseData, $transaction);
                    }

                    if (!$result) {
                        Log::alert(json_encode([
                            "message" => 'Payment status confirmation failed',
                            "Transaction Type" => $transaction->transaction_type->description,
                            "paystack_response" => $responsePayload
                        ]));
                    }
                }
                $requestResponse->status = true;
                $requestResponse->message = $responsePayload['message'];
                $requestResponse->data = $responsePayload;
            } else {
                $requestResponse->message = 'Not a card transaction';
                $transaction->update([
                    'transaction_status_id' => TransactionStatus::COMPLETED
                ]);
            }

            $confirmationData = [
                'status' => 'success',
                'title' => 'Ok',
                'message' => $requestResponse->message,
                'data' => $requestResponse->data ?? null
            ];
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errors = flattenArray(array_values((array)$errors));
            $message = $errors[0];
            $confirmationData = [
                'status' => 'error',
                'title' => 'Ok',
                'message' => $message,
            ];

        } catch(\Exception $e) {
            logCriticalError('Something went wrong', $e);
            $confirmationData = [
                'status' => 'error',
                'title' => 'Ok',
                'message' => 'Something went wrong : ' . $e->getMessage(),
            ];
        }
        return back()->with(['payment_confirmation_data' => $confirmationData]);
    }
}
