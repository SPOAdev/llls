<?php

namespace App\Console\Commands;

use App\Models\License;

use Illuminate\Console\Command;

class LicenseUpdatePayloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:update-payload 
                            {license_key : License key to update} 
                            {--data=* : Payload parameters in key=value format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the license update_payload field with custom data';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $licenseKey = $this->argument('license_key');
        $data = [];

        foreach ($this->option('data') as $item) {
            if (strpos($item, '=') !== false) {
                [$key, $value] = explode('=', $item, 2);
                $data[$key] = $value;
            }
        }

        if (empty($data)) {
            $this->error('No data provided.');
            return;
        }

        $license = License::where('license_key', $licenseKey)->first();

        if (!$license) {
            $this->error('License not found.');
            return;
        }

        $license->update_payload = $data;
        $license->save();

        $this->info('License update_payload has been successfully updated.');
    }
}
