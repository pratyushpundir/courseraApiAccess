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
        $schedule->command('coursera:update --recordType=all')
                 ->appendOutputTo(storage_path('logs/coursera/updates.log'))
                 ->emailOutputTo(env('SECONDARY_ADMIN_EMAIL'))
                 ->timezone('America/Chicago')
                 ->dailyAt('10:00');

        $schedule->command('coursera:export --recordType=all')
                 ->appendOutputTo(storage_path('logs/coursera/exports.log'))
                 ->emailOutputTo(env('SECONDARY_ADMIN_EMAIL'))
                 ->timezone('America/Chicago')
                 ->monthly();
    }
}
