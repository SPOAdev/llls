<?php
return [
  	// Debug directive from .env file
    'debug' => env('LLLS_DEBUG', false),
  	
  	// License duration cycles (used when assigning expirations dynamically)
    'cycles' => [
        'daily' => fn () => now()->addDay(),
        'weekly' => fn () => now()->addWeek(),
        'monthly' => fn () => now()->addMonth(),
        'quarterly' => fn () => now()->addMonths(3),
        'semiannually' => fn () => now()->addMonths(6),
        'annually' => fn () => now()->addYear(),
    ],
  	
  	// Defines how often the license cleanup runs (via Laravel scheduler)
  	'check_license_schedule' => 'daily',
  
  	// Webhooks
  	'webhooks' => [
        'hotmart' => [
            'events' => [
                'PURCHASE_APPROVED' => 'log_only',
                'PURCHASE_COMPLETE' => 'create_license',
                'PURCHASE_REFUNDED' => 'cancel_license',
                'PURCHASE_CANCELED' => 'cancel_license',
                'PURCHASE_CHARGEBACK' => 'cancel_license',
                'SUBSCRIPTION_CANCELLATION' => 'cancel_license',
                'SWITCH_PLAN' => 'update_license',
                'UPDATE_SUBSCRIPTION_CHARGE_DATE' => 'update_license',
            ],
        ],
    ],
];
