<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;

class ProcessUserNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $notifiables;
    protected $notificationClass;
    protected $notificationClassParams;

    /**
     * Create a new job instance.
     * @param Model|Model[]|Employee|Employee[]|User|User[] $notifiables
     * @param string $notificationClass
     * @param array $notificationClassParams
     *
     * @return void
     */
    public function __construct($notifiables, $notificationClass, $notificationClassParams)
    {
        $this->notifiables = $notifiables;
        $this->notificationClass = $notificationClass;
        $this->notificationClassParams = $notificationClassParams;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Notification::send($this->notifiables, new $this->notificationClass(...$this->notificationClassParams) );
    }
}
