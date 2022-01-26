<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('loyalty_offers:manage')->dailyAt('00:01');
        $schedule->command('ready-for-pickup:add_to_queue')->everyTenMinutes();
        $schedule->command('ready-for-pickup:notify')->everyThirtyMinutes();
        if(isLocalOrDev()) {
            $schedule->command('order_request:simulate_status_change')->everyFiveMinutes()->withoutOverlapping();
        }

        if(!isLocalOrDev() && config('app.CHUNK_ORDERS_IMPORT')) {
            $schedule->command("migration:old_data --table=orders")->everyMinute()->withoutOverlapping();
        }

        if(config('app.SEND_DAILY_AND_MONTHLY_REPORT')) {
            $schedule->command('send:daily-report')->dailyAt('22:00');
            $schedule->command('send:monthly-report')->monthlyOn(1, '00:01');
        }
        if(config('app.PROCESS_EXISTING_KWIK_ORDERS')) {
            $schedule->command('get_all_existing_kwik_orders')->everyFiveMinutes()->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
