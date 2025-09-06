<?php

namespace App\Console;

use Bugsnag\BugsnagLaravel\OomBootstrapper;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('notification:teacher-conduct-missing')->dailyAt('10:00');
        $schedule->command('notification:payment-reminder')->dailyAt('19:50');
        $schedule->command('notification:unplanned-or-cancelled')->dailyAt('20:00');
        $schedule->command('notification:pass-notification')->dailyAt('20:30');

        $schedule->command('app:check-errors')->dailyAt('03:00');
        $schedule->command('app:send-telegram-messages')->everyMinute();
        $schedule->command('app:sync-sms')->hourlyAt(30);

        $schedule->command('scout:reimport-all')->dailyAt('01:00');

        $schedule->command('app:teacher-stats')->weeklyOn(7, '05:00');

        // first lesson
        $schedule->command('notification:first-lesson 2d')->dailyAt('20:00');
        $schedule->command('notification:first-lesson 1d')->dailyAt('20:00');
        $schedule->command('notification:first-lesson 20min')->everyFiveMinutes()->between('08:30', '20:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }

    protected function bootstrappers()
    {
        return array_merge(
            [OomBootstrapper::class],
            parent::bootstrappers(),
        );
    }
}
