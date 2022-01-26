<?php

namespace App\Observers;

use App\Jobs\ProcessUserNotification;
use App\Models\Employee;
use Illuminate\Support\Facades\Queue;

class EmployeeObserver
{
    /**
     * Handle the employee "created" event.
     *
     * @param  \App\Models\Employee  $employee
     * @return void
     */
    public function created(Employee $employee)
    {
        Queue::push(
            new ProcessUserNotification(
                $employee,
                '\App\Notifications\SetupPasswordNotification',
                [$employee->getResetToken()]
            )
        );
    }
}
