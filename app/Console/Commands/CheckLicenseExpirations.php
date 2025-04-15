<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    protected $description = 'Handle license expirations';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        \App\Models\License::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'inactive']);

        $this->info('Licencias expiradas desactivadas');
    }

}
