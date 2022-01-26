<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DropoffOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;

    /**
     * DropoffOrderCreated constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('email.orders.dropoff_created')
            ->with([
                'orderDate' => $this->order->created_at->format('Y-m-d'),
                'qrCode' => QrCode::format('png')->size(100)->generate($this->order->id)
            ]);
    }
}
