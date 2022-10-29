<?php

namespace App\Console;

use App\Console\Commands\timeoutTripRequest;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use \Spatie\ShortSchedule\ShortSchedule;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        timeoutTripRequest::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // $schedule->command(command:'ChangeDriverForRequest')->everyMinute();
        // $schedule->command(command:'command:test')->everyMinute();
        // $shortSchedule->command(command:'command:test')->everySecond(20);
    }

    protected function shortSchedule(ShortSchedule $shortSchedule, Schedule $schedule)
    {
        $shortSchedule->command('command:test')->everySeconds(20);

        $schedule->command(command:'TripRequest:timeout')->everyMinute();
        $schedule->command(command:'changeStatusDriver')->everyMinute();
        $schedule->command(command:'StatusDriver')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
