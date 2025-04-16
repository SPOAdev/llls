<?php

namespace App\Http\Controllers\Api;

use App\Services\HotmartWebhookService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HotmartWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (config('llls.debug')) {
            Log::info('Hotmart Webhook Raw', [
                'ip' => $request->ip(),
                'payload' => $request->all(),
            ]);
        }

        $event = $request->input('event');

        app(HotmartWebhookService::class)->handle($event, $request->all());

        return response()->json(['message' => 'OK']);
    }
}
