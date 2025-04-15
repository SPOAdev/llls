<?php
return [
    'debug' => env('LLLS_DEBUG', true),
    'product_model' => null, // puede ser App\Models\Product::class
    'cycles' => [
        'daily' => fn () => now()->addDay(),
        'weekly' => fn () => now()->addWeek(),
        'monthly' => fn () => now()->addMonth(),
        'quarterly' => fn () => now()->addMonths(3),
        'semiannually' => fn () => now()->addMonths(6),
        'annually' => fn () => now()->addYear(),
    ],
  	'check_license_schedule' => 'daily',
];

