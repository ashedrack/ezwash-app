<?php

namespace App\Rules;

use App\Models\Order;
use Illuminate\Contracts\Validation\Rule;

class OrderValidForPayment implements Rule
{
    protected $errorMessage;
    protected $authUser;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($authUser)
    {
        $this->authUser = $authUser;
        $this->errorMessage = 'Invalid :attribute selected';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $order = Order::where('id', $value)->where('user_id', $this->authUser->id)->first();
        if(empty($order)){
            return false;
        }
        elseif ($order->status === ORDER_STATUS_COMPLETED){
            $this->errorMessage = 'Attempting payment on completed order';
            return false;
        }
        elseif ($order->amount <= 0) {
            $this->errorMessage = 'Order amount is not valid';
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
