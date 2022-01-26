<?php

namespace App\Notifications;

use App\Channels\OneSignalChannel;
use App\Classes\OneSignalHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ReadyForPickupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    public function viaQueues()
    {
        return [
            'mail' => 'notification-queue',
        ];
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
     * @param  User $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting(Lang::getFromJson('Hello '. $notifiable->name . ','))
            ->subject(Lang::getFromJson('Order is ready for pickup'))
            ->line(Lang::getFromJson('Your order #'. $this->order->id. ' is ready for pickup, please come by the laundromat or request a delivery from the mobile app as soon as possible.'))
            ->line(Lang::getFromJson('Thank you for your continued patronage.'));
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
            "Wash Order Completed",
            Lang::trans('notification.order_ready_for_pickup'),
            [
                "message" => Lang::trans('notification.order_ready_for_pickup'),
                "order_id" => $this->order->id,
                "user_id" => $notifiable->id,
                "notification_type" => "dropoff_complete"
            ]
        );
    }
}
