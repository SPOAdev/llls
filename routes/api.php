<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LicenseController;

Route::post('verify', [LicenseController::class, 'verify']);
