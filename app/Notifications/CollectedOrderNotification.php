<?php

namespace App\Notifications;

use App\Channels\OneSignalChannel;
use App\Classes\OneSignalHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class CollectedOrderNotification extends Notification
{
    use Queueable;

    protected $subject;
    protected $message;
    protected $order;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->subject = 'Order Collected';
        $this->message = 'You have picked up your order, thank you for your business!!!';
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', OneSignalChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Collected')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('You have picked up your order, thank you for your business!!!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  User $notifiable
     */
    public function toOneSignal($notifiable)
    {
        OneSignalHelper::sendNotificationCustom(
            $notifiable->notification_player_id,
            $this->subject,
            $this->message,
            [
                "message" => $this->message,
                "order_id" => $this->order->id,
                "user_id" => $notifiable->id,
                "notification_type" => "order_collected"
            ]
        );
    }
}
