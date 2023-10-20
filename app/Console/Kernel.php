<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->call('App\Http\Controllers\OutlookCalendarController@refreshToken')->everyMinute(); // Adjust the frequency as needed
    }
    
    

    /**
     * Register the commands for the application. 
     */
  
    
}
