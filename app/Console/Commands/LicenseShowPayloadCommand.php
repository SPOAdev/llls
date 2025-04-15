<?php

namespace App\Console\Commands;

use App\Models\License;

use Illuminate\Console\Command;

class LicenseShowPayloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:show-payload 
                            {license_key : License key to display update_payload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display the license update_payload content';

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

        if (empty($license->update_payload)) {
            $this->info('This license does not have an update_payload.');
            return;
        }

        $this->info('Update Payload:');
        $this->line(json_encode($license->update_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
