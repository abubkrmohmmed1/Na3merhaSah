<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendB2BWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 120, 300]; // تأخير تصاعدي عند الفشل

    protected string $webhookUrl;
    protected array $payload;
    protected string $companyId;

    public function __construct(string $webhookUrl, array $payload, string $companyId)
    {
        $this->webhookUrl = $webhookUrl;
        $this->payload = $payload;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        $payloadJson = json_encode($this->payload);
        $signature = hash_hmac('sha256', $payloadJson, $this->companyId);

        $response = Http::withHeaders([
            'X-Namerha-Signature' => "sha256={$signature}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->timeout(10)->post($this->webhookUrl, $this->payload);

        if ($response->failed()) {
            Log::warning("B2B Webhook delivery attempt failed for company {$this->companyId} to {$this->webhookUrl}. Status: " . $response->status());
            throw new \Exception("Webhook failed with status: " . $response->status());
        }
    }
}
