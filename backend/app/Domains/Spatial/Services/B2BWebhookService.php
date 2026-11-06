<?php

namespace App\Domains\Spatial\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class B2BWebhookService
{
    /**
     * Dispatch verification result to the company's webhook URL.
     */
    public static function dispatchResult(string $orderId, array $resultData)
    {
        $company = \Illuminate\Support\Facades\DB::table('verification_orders')
            ->join('companies', 'verification_orders.company_id', '=', 'companies.id')
            ->where('verification_orders.id', $orderId)
            ->select('companies.id', 'companies.webhook_url')
            ->first();

        if ($company && $company->webhook_url) {
            $payload = [
                'event' => 'verification.completed',
                'order_id' => $orderId,
                'result' => $resultData,
                'timestamp' => now()->toIso8601String()
            ];

            \App\Jobs\SendB2BWebhookJob::dispatch($company->webhook_url, $payload, $company->id);
        }
    }
}
