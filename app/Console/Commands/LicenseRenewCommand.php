<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use Carbon\Carbon;

class LicenseRenewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:renew {license_key}';
  

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually renew a license by license_key using configured cycle';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $key = $this->argument('license_key');

        $license = License::where('license_key', $key)->first();

        if (!$license) {
            $this->error("License not found: {$key}");
            return;
        }

        $cycle = $license->validation_rules['cycle'] ?? 'lifetime';
        $cycles = config('llls.cycles');

        if (!array_key_exists($cycle, $cycles)) {
            $this->error("Cycle '{$cycle}' not configured in llls.php");
            return;
        }

        $nextDate = Carbon::now()->{$cycles[$cycle]}();
        $license->update(['expires_at' => $nextDate]);

        $this->info("License renewed until: {$nextDate->toDateTimeString()}");
        llls_log('License manually renewed via CLI', [
            'license_key' => $license->license_key,
            'cycle' => $cycle,
            'new_expires_at' => $nextDate->toDateTimeString(),
        ]);
    }
}