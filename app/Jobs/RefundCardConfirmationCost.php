<?php

namespace App\Jobs;

use App\Classes\PaystackRequestHandler;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RefundCardConfirmationCost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction;
    /**
     * Create a new job instance.
     *
     * @param Transaction | Model $transaction
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the Job
     *
     * @return int
     */
    public function handle()
    {
        try {
            logger(json_encode($this->transaction));
            if ($this->transaction->transaction_type_id !== TransactionType::NEW_CARD_ID) {
                return -1;
            }
            if($this->transaction->transaction_status_id !== TransactionStatus::REFUNDED){
                $result = (new PaystackRequestHandler())->refundTransaction($this->transaction->reference_code);
                if($result['status']){
                    $this->transaction->update([
                        'transaction_status_id' => TransactionStatus::REFUNDED
                    ]);
                    return 0;
                } else {
                    logger(json_encode($result));
                }
            }
        } catch (\Exception $e) {
            logExceptionToDatabase($e->getMessage(), $e->getLine(), $e->getFile(), $e->getTraceAsString(), [$this->transaction]);
            return -1;
        }
    }
}
