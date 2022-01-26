<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class CustomerPasswordResetNotification extends Notification
{
    use Queueable;
    /**
     * The password setup token.
     *
     * @var string
     */
    public $token;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $message;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
                    ->greeting('Hello '. $notifiable->name . ',')
                    ->line('You are receiving this email because we received a password reset request for your account.')
                    ->action('Reset Password', route('customer_account.reset_password', ['token' => $this->token, 'email' => $notifiable->email]))
                    ->line('This password reset link will expire within '.config('auth.reset_password_timer').'hours.')
                    ->line('If you did not request a password reset, no further action is required.')
                    ->line('Thank you for using our application!');

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
}
