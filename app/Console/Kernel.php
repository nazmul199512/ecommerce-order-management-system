<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Check for low stock every hour
        $schedule->job(new \App\Jobs\CheckLowStockJob)->hourly();

        // Clean up old failed jobs weekly
        $schedule->command('queue:prune-failed --hours=168')->weekly();

        // Generate daily reports
        $schedule->command('reports:daily')->dailyAt('23:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
