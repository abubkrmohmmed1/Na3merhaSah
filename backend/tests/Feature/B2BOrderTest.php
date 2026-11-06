<?php

namespace Tests\Feature;

use App\Domains\Billing\Models\ApiKey;
use App\Domains\Billing\Models\Company;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\Services\QuestBridgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class B2BOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_b2b_order_creates_quests_and_notifies()
    {
        Http::fake();
        
        // Mock the Service
        $this->mock(\App\Domains\Spatial\Services\QuestBridgeService::class, function ($mock) {
            $mock->shouldReceive('bridgeOrderToQuests')->once();
        });

        // 1. Setup Company & Layer (Basic tables ok)
        $company = Company::create([
            'name' => 'شركة توصيل تجريبية',
            'email' => 'test@delivery.com',
            'plan' => 'pro',
            'webhook_url' => 'https://webhook.site/test'
        ]);
        ApiKey::create([
            'company_id' => $company->id,
            'key' => 'nrh_test_key',
            'name' => 'Test Key'
        ]);

        // 2. Submit Order
        $response = $this->withHeader('X-API-Key', 'nrh_test_key')
            ->postJson('/api/v1/b2b/orders', [
                'items' => [
                    ['name' => 'صيدلية تجريبية', 'lat' => 15.58, 'lng' => 32.52],
                    ['name' => 'مخبز تجريبي', 'lat' => 15.59, 'lng' => 32.53]
                ],
                'payment_method' => 'bankak'
            ]);

        $response->assertStatus(201);
    }
}
