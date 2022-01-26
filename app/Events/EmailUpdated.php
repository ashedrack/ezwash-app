<?php

namespace App\Events;


class EmailUpdated
{
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Employee|\App\Models\User  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
