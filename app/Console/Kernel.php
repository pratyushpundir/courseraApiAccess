<?php

namespace UNELearning\Console;

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
        Commands\UpdateCourseraData::class,
        Commands\ExportCourseraData::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('coursera:update --recordType=all')->withoutOverlapping()
                 ->dailyAt('08:00')
                 ->timezone('America/Chicago');
        
        $schedule->command('coursera:export --recordType=all')->withoutOverlapping()
                 ->monthlyOn(6, '09:00')
                 ->timezone('America/Chicago');
    }
}
