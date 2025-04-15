<?php
return [
    'debug' => env('LLLS_DEBUG', false),
  	
  	// Optional model relationship to link licenses to products
    'product_model' => null, // can be App\Models\Product::class
  	
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
];

