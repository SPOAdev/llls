<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use App\Models\Product;
use Carbon\Carbon;

class CheckLicenseExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licenses:check-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and deactivate expired licenses';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $count = 0;
        $now = Carbon::now();
        $cycles = config('llls.cycles');

        $licenses = License::where('status', 'active')->get();

        foreach ($licenses as $license) {
            $shouldDeactivate = false;

            $product = $license->product;

            if (!$license->product_id || optional($product)->provider === 'local') {
                $cycle = $license->validation_rules['cycle'] ?? null;
                if ($cycle && isset($cycles[$cycle])) {
                    $expected = $license->expires_at ?? $license->created_at;
                    $expectedNext = Carbon::parse($expected)->{$cycles[$cycle]}();
                    if ($expectedNext->isPast()) {
                        $shouldDeactivate = true;
                    }
                }
            } else {
                if ($license->expires_at && $license->expires_at->isPast()) {
                    $shouldDeactivate = true;
                }
            }

            if ($shouldDeactivate) {
                $license->update(['status' => 'inactive']);
                llls_log('License auto-deactivated by expiration check', [
                    'license_key' => $license->license_key,
                    'user_id' => $license->user_id,
                ]);
                $count++;
            }
        }

        $this->info("{$count} expired licenses deactivated.");
    }
}
