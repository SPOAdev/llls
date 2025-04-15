<?php

namespace App\Console\Commands;

use App\Models\License;

use Illuminate\Console\Command;

class LicenseClearPayloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:clear-payload 
                            {license_key : License key to clear update_payload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the update_payload field of a license';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $licenseKey = $this->argument('license_key');

        $license = License::where('license_key', $licenseKey)->first();

        if (!$license) {
            $this->error('License not found.');
            return;
        }

        $license->update_payload = null;
        $license->save();

        $this->info('License update_payload has been cleared.');
    }
}
