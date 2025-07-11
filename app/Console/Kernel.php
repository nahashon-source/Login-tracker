<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ImportUsersCommand::class,
        \App\Console\Commands\ImportSignInsCommand::class,
        \App\Console\Commands\DeleteDummyUsers::class,
        \App\Console\Commands\DeleteDummySignIns::class,
        \App\Console\Commands\ImportUsers::class,
        




    ];
    
    protected function schedule(Schedule $schedule)
    {
        // Define scheduled tasks here
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
