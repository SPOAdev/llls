<?php

namespace App\Services;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class HotmartWebhookService
{
    public function handle(string $event, array $payload): void
    {
        $action = config('llls.webhooks.hotmart.events')[$event] ?? 'log_only';

        if (!method_exists($this, $action)) {
            llls_log("Hotmart Event with no handler: {$event}");
            return;
        }

        llls_log("Hotmart Event Dispatched: {$event} -> {$action}");

        $this->{$action}($payload);
    }

    public function log_only(array $payload): void
    {
        llls_log('Hotmart log_only action', ['payload' => $payload]);
    }

    public function create_license(array $payload): void
    {
        llls_log('Hotmart create_license action', ['payload' => $payload]);

        $data = $payload['data'] ?? [];

        $email = $data['buyer']['email']
              ?? $data['subscriber']['email']
              ?? $data['subscription']['user']['email']
              ?? null;

        if (!$email) {
            llls_log('Hotmart create_license without email', ['payload' => $payload]);
            return;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $data['buyer']['name'] ?? $data['subscriber']['name'] ?? 'Hotmart User',
                'password' => bcrypt(str()->random(16)),
            ]
        );

        $productExternalId = $data['product']['id'] ?? $data['product']['ucode'] ?? null;
        $productName = $data['product']['name'] ?? 'Hotmart Product';

        if ($productExternalId === null) {
            llls_log('Hotmart create_license without product external identifier', ['payload' => $payload]);
            return;
        }

        $product = Product::firstOrCreate(
            [
                'provider' => 'hotmart',
                'external_id' => $productExternalId,
            ],
            [
                'name' => $productName,
                'metadata' => $data['product'],
            ]
        );

        $expiresAt = null;
        $cycle = 'lifetime';

        if (!empty($data['subscription'])) {
            if (!empty($data['date_next_charge'])) {
                $expiresAt = Carbon::createFromTimestampMs($data['date_next_charge']);
                $cycle = 'monthly';
            }
        }

        $licenseKey = strtoupper(str()->uuid());

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_key' => $licenseKey,
            'validation_rules' => [
                'domain_mode' => 'disabled',
                'cycle' => $cycle,
            ],
            'status' => 'active',
            'expires_at' => $expiresAt,
            'metadata' => [
                'subscriber_code' => $data['subscription']['subscriber']['code'] ?? null,
                'subscription_id' => $data['subscription']['id'] ?? null,
                'subscription_status' => $data['subscription']['status'] ?? null,
                'current_plan_name' => $data['subscription']['plan']['name'] ?? null,
                'current_plan_id' => $data['subscription']['plan']['id'] ?? null,
                'plans' => $data['plans'] ?? [],
                'switch_plan_date' => isset($data['switch_plan_date']) ? Carbon::createFromTimestampMs($data['switch_plan_date'])->toDateTimeString() : null,
                'id' => $data['product']['id'] ?? null,
                'ucode' => $data['product']['ucode'] ?? null,
            ],
        ]);

        llls_log('Hotmart license created', [
            'license_id' => $license->id,
            'license_key' => $licenseKey,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'expires_at' => $expiresAt?->toDateTimeString(),
        ]);
    }

    public function cancel_license(array $payload): void
    {
        llls_log('Hotmart cancel_license action', ['payload' => $payload]);

        $data = $payload['data'] ?? [];

        $email = $data['buyer']['email']
              ?? $data['subscriber']['email']
              ?? $data['subscription']['user']['email']
              ?? null;

        if (!$email) {
            llls_log('Hotmart cancel_license without email', ['payload' => $payload]);
            return;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            llls_log('Hotmart cancel_license user not found', ['email' => $email]);
            return;
        }

        $productExternalId = $data['product']['id'] ?? $data['product']['ucode'] ?? null;
        if ($productExternalId === null) {
            llls_log('Hotmart cancel_license without product id', ['payload' => $payload]);
            return;
        }

        $product = Product::where('provider', 'hotmart')
            ->where('external_id', $productExternalId)
            ->first();

        if (!$product) {
            llls_log('Hotmart cancel_license product not found', ['external_id' => $productExternalId]);
            return;
        }

        $license = License::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if (!$license) {
            llls_log('Hotmart cancel_license license not found', [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
            return;
        }

        $license->update(['status' => 'inactive']);

        llls_log('Hotmart license cancelled', [
            'license_id' => $license->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function update_license(array $payload): void
    {
        llls_log('Hotmart update_license action', ['payload' => $payload]);

        $data = $payload['data'] ?? [];

        $email = $data['subscription']['user']['email'] ?? $data['subscriber']['email'] ?? null;
        if (!$email) {
            llls_log('Hotmart update_license without user email', ['payload' => $payload]);
            return;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            llls_log('Hotmart update_license user not found', ['email' => $email]);
            return;
        }

        $productExternalId = $data['subscription']['product']['id'] ?? null;
        if ($productExternalId === null) {
            llls_log('Hotmart update_license without product id', ['payload' => $payload]);
            return;
        }

        $product = Product::where('provider', 'hotmart')
            ->where('external_id', $productExternalId)
            ->first();

        if (!$product) {
            llls_log('Hotmart update_license product not found', ['external_id' => $productExternalId]);
            return;
        }

        $license = License::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if (!$license) {
            llls_log('Hotmart update_license license not found', [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
            return;
        }

        $metadata = $license->metadata ?? [];

        $metadata['current_plan_name'] = $data['subscription']['plan']['name'] ?? $metadata['current_plan_name'] ?? null;
        $metadata['current_plan_id'] = $data['subscription']['plan']['id'] ?? $metadata['current_plan_id'] ?? null;
        $metadata['plans'] = $data['plans'] ?? $metadata['plans'] ?? [];
        $metadata['switch_plan_date'] = isset($data['switch_plan_date']) ? Carbon::createFromTimestampMs($data['switch_plan_date'])->toDateTimeString() : ($metadata['switch_plan_date'] ?? null);

        if (!empty($data['subscription']['date_next_charge'])) {
            $license->expires_at = Carbon::createFromTimestampMs($data['subscription']['date_next_charge']);
        }

        $license->update(['metadata' => $metadata]);

        llls_log('Hotmart license updated', [
            'license_id' => $license->id,
        ]);
    }
}
