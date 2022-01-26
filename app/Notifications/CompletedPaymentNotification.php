<?php

namespace App\Notifications;

use App\Channels\OneSignalChannel;
use App\Classes\OneSignalHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CompletedPaymentNotification extends Notification
{
    use Queueable;
    protected $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
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
            ->subject($this->data['subject'] ?? "Payment Received Successful")
            ->greeting('Hello '. $notifiable->name . ',')
            ->line($this->data['message'])
            ->line('Thank you for your business.');
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
            $this->data['subject'] ?? "Payment Received Successful",
            $this->data['message'],
            [
                "message" => $this->data['message'],
                "user_id" => $notifiable->id,
                "notification_type" => $this->data['notification_type']
            ]
        );
    }
}
