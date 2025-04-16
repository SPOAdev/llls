<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\HotmartWebhookController;

Route::post('verify', [LicenseController::class, 'verify']);
Route::post('/webhook/hotmart', [HotmartWebhookController::class, 'handle']);
