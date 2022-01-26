<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class SetupPasswordNotification extends Notification
{
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
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return (new MailMessage)
            ->greeting(Lang::getFromJson('Hello '. $notifiable->name . ','))
            ->subject(Lang::getFromJson('Setup Account Notification'))
            ->line(Lang::getFromJson('You have been added as an employee at Ezwash Laundromat. Please click the button below to complete your account setup'))
            ->action(Lang::getFromJson('Setup Account'), route('account.setup', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line(Lang::getFromJson('This account setup link will expire in :count hours.', ['count' => (int)config('auth.passwords.'.config('auth.defaults.passwords').'.expire')/60]));
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
