<?php

namespace App\Notifications;

use App\Channels\OneSignalChannel;
use App\Classes\OneSignalHelper;
use App\Models\LoyaltyOffer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewSpecialOfferApplied extends Notification implements ShouldQueue
{
    use Queueable;
    protected $subject;
    protected $message;

    /**
     * NewSpecialOfferApplied constructor.
     *
     * @param LoyaltyOffer $offer
     */
    public function __construct(LoyaltyOffer $offer)
    {
        $this->subject = "Special Discount '{$offer->display_name}'";
        $discountValue = number_format($offer->discount_value, 2);
        $this->message = "You have received a discount up to ₦{$discountValue} for your next order";

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
     * @param  mixed|User  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->greeting('Hello '. $notifiable->name)
                    ->subject($this->subject)
                    ->line($this->message);
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
                "user_id" => $notifiable->id,
                "notification_type" => "special_offer"
            ]
        );
    }
}
