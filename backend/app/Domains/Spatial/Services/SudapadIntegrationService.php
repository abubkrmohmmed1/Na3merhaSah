<?php

namespace App\Domains\Spatial\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for Sudapad National Addressing System Integration
 */
class SudapadIntegrationService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('namerha.sudapad.url');
        $this->apiKey = config('namerha.sudapad.key');
    }

    /**
     * Request verification of a feature from Sudapad
     */
    public function requestVerification(string $featureId, array $coords)
    {
        return $this->call('POST', '/verify', [
            'feature_id' => $featureId,
            'lat' => $coords['lat'],
            'lng' => $coords['lng'],
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Send verified field data to Sudapad
     */
    public function sendFieldData(string $featureId, array $data)
    {
        return $this->call('POST', '/sync', [
            'feature_id' => $featureId,
            'data' => $data,
            'verified_at' => now()->toIso8601String()
        ]);
    }

    /**
     * General API caller for Sudapad
     */
    private function call(string $method, string $endpoint, array $payload)
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->withHeaders(['Accept' => 'application/json'])
                ->$method($this->baseUrl . $endpoint, $payload);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error("Sudapad API Error: " . $response->body());
            return ['status' => 'error', 'message' => 'Sudapad integration failed'];
        } catch (\Exception $e) {
            Log::error("Sudapad Connection Failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Connection failed'];
        }
    }
}
