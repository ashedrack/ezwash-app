<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPaymentRequired extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public $greeting;
    /**
     * Create a new message instance.
     *
     * @param Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->greeting = 'Hello ' . $order->user->name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //Not sure is this email is actually needed....
        return $this->from('noreply@ezwashndry.com')
            ->view('email.default')
            ->with([
               'message' => 'Your order has been placed successfully, please visit the app to pay'
            ]);
    }
}
