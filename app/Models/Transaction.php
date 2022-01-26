<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class Transaction
 *
 * @property int $id
 * @property int $transaction_type_id
 * @property int $order_id
 * @property int $user_id
 * @property float $amount
 * @property string $metadata
 * @property string $header
 * @property string $message
 * @property int $transaction_status_id
 * @property string $reference_code
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 *
 * @property User $user
 * @property Order $order
 * @property UserCard $user_card
 * @property TransactionType $transaction_type
 * @property TransactionStatus $transactionStatus
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Transaction extends Model
{
//	use \Illuminate\Database\Eloquent\SoftDeletes;

	protected $casts = [
		'transaction_type_id' => 'int',
		'order_id' => 'int',
		'user_id' => 'int',
		'amount' => 'float',
		'transaction_status_id' => 'int',
        'transaction_payment_method_id' => 'int'
	];

	protected $fillable = [
		'transaction_type_id',
        'card_id',
		'order_id',
		'user_id',
		'amount',
		'metadata',
		'header',
		'message',
		'transaction_status_id',
		'reference_code',
        'transaction_payment_method_id'
	];

	protected $with = [
	    'transactionStatus', 'order', 'user'
    ];

    /**
     * Scope a query to only include transactions the user has access to.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  Auth|Employee $authUser
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAllowed($query, $authUser)
    {
        if($authUser->can('list_companies')){
            return $query;
        }elseif($authUser->can('list_locations')){
            return $query->whereHas('order', function ($q) use ($authUser) {
                $q->where('company_id', $authUser->company_id);
            });
        }
        return $query->whereHas('order', function ($q) use ($authUser) {
            $q->where('location_id', $authUser->location_id);
        });
    }

	public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionStatus()
    {
        return $this->belongsTo(TransactionStatus::class, 'transaction_status_id');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'transaction_payment_method_id');
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function user_card()
    {
        return $this->belongsTo(UserCard::class, 'card_id');
    }
}
