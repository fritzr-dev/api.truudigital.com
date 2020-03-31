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
        // $schedule->command('inspire')
        //          ->hourly();
        
        /*migrate project to DB and hubstaff*/
        $schedule->command('accelohub:projects')->twiceDaily(1, 13);
        /*migrate project task to DB*/
        $schedule->command('accelohub:projects tasks')->everyFifteenMinutes();
        /*migrate tickets to DB*/
        $schedule->command('accelohub:projects tickets')->everyFifteenMinutes();

        /*migrate taskDB to hubstaff*/
        $schedule->command('accelohub:projects tasks2Hubstaff')->everyFifteenMinutes();
        #$schedule->command('accelohub:projects taskticket2Hubstaff')->hourly();
        
        /*migrate timesheetDB to Accelo*/
        $schedule->command('accelohub:projects timesheet2Accello')->twiceDaily(1, 13);
        
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
