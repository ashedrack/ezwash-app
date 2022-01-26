<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;

class SendPasswordSetupEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @param string $broker
     * @return void
     */
    public function handle(Registered $event)
    {
        $event->user->sendPasswordSetupNotification();
    }
}
