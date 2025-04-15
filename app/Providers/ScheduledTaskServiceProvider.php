<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduledTaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->schedule(app(Schedule::class));
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command('licenses:check-expirations')
            ->{$this->getScheduleMethod()}();
    }

    protected function getScheduleMethod(): string
    {
        $method = config('llls.check_license_schedule', 'hourly');

        return method_exists(\Illuminate\Console\Scheduling\Event::class, $method)
            ? $method
            : 'hourly';
    }
}
