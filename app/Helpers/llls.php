<?php

// Licenciable product providers
if (!function_exists('llls_providers')) {
    function llls_providers(): array
    {
        return array_merge(['local'], array_keys(config('llls.webhooks', [])));
    }
}

// Global logger
if (!function_exists('llls_log')) {
    function llls_log(string $message, array $context = []): void
    {
        if (config('llls.debug')) {
            \Illuminate\Support\Facades\Log::info($message, $context);
        }
    }
}