<?php

namespace App\Http\Controllers\Api;

use App\Models\License;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LicenseController extends Controller
{
    public function verify(Request $request)
    {
      $request->validate([
        'license_key' => 'required|string',
        'domain'      => 'nullable|string',
      ]);

      $license = License::where('license_key', $request->license_key)->first();

      if (!$license) {
        $this->logVerification(null, $request, 'invalid', 'License not found');

        return response()->json([
          'status'  => 'invalid',
          'message' => 'License not found',
        ]);
      }

      if ($license->status !== 'active') {
        $this->logVerification($license, $request, $license->status, 'License is not active');

        return response()->json([
          'status'  => $license->status,
          'message' => 'License is not active',
          'expires_at' => optional($license->expires_at)->toDateTimeString(),
        ]);
      }

      if ($license->expires_at && $license->expires_at->isPast()) {
        $this->logVerification($license, $request, 'inactive', 'License has expired');

        return response()->json([
          'status'  => $license->status,
          'message' => 'License has expired',
          'expires_at' => optional($license->expires_at)->toDateTimeString(),
        ]);
      }

      $rules = $license->validation_rules ?? [];

      if (!empty($rules['domain_mode']) && $rules['domain_mode'] !== 'disabled') {
        $domain = $request->domain;

        if (!$domain) {
          $this->logVerification($license, $request, 'invalid', 'Domain is required');

          return response()->json([
            'status'  => 'invalid',
            'message' => 'Domain is required for this license',
          ]);
        }

        if ($rules['domain_mode'] === 'single') {
          if (empty($rules['domain'])) {
            $rules['domain'] = $domain;
            $license->validation_rules = $rules;
            $license->save();
          } elseif ($rules['domain'] !== $domain) {
            $this->logVerification($license, $request, 'invalid', 'Domain not allowed');

            return response()->json([
              'status'  => 'invalid',
              'message' => 'Domain not allowed for this license',
              'expected_domain' => $rules['domain'],
            ]);
          }
        }

        if ($rules['domain_mode'] === 'multi') {
          if (empty($rules['domains']) || !in_array($domain, $rules['domains'])) {
            $this->logVerification($license, $request, 'invalid', 'Domain not in allowed list');

            return response()->json([
              'status'  => 'invalid',
              'message' => 'Domain not allowed for this license',
              'allowed_domains' => $rules['domains'] ?? [],
            ]);
          }
        }
      }

      $this->logVerification($license, $request, 'valid', 'License is valid');

      return response()->json([
        'status'     => 'valid',
        'message'    => 'License is valid',
        'expires_at' => optional($license->expires_at)->toDateTimeString(),
        'update'     => $license->update_payload ?? null,
      ]);
    }

    protected function logVerification($license, Request $request, string $status, string $message): void
    {
      if (!config('llls.debug')) {
        return;
      }

      Log::info('License verification', [
        'license_key' => $license->license_key ?? $request->input('license_key'),
        'user_id'     => $license->user_id ?? null,
        'domain'      => $request->input('domain'),
        'ip'          => $request->ip(),
        'status'      => $status,
        'message'     => $message,
      ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(License $license)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(License $license)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, License $license)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(License $license)
    {
        //
    }
}
