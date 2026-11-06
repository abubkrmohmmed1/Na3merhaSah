<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Services\ExternalVerificationService;
use App\Domains\Spatial\Services\ConfidenceEngine;
use Illuminate\Support\Facades\DB;

class VerifyFeatureWithOSMJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // تأخير 60 ثانية قبل إعادة المحاولة تجنباً لـ Rate limit

    protected string $featureId;

    public function __construct(string $featureId)
    {
        $this->featureId = $featureId;
    }

    public function handle(): void
    {
        $feature = GeoFeature::find($this->featureId);
        if (!$feature || !$feature->geom) {
            return;
        }

        $props = $feature->properties ?? [];
        if (isset($props['external_checked_osm'])) {
            return; // تم فحصه مسبقاً
        }

        $coords = DB::selectOne("SELECT ST_Y(geom) as lat, ST_X(geom) as lng FROM geo_features WHERE id = ?", [$this->featureId]);
        if (!$coords) {
            return;
        }

        $osm = ExternalVerificationService::checkOSM($coords->lat, $coords->lng, $feature->mapLayer->name ?? '');
        
        $props['external_checked_osm'] = true;
        if ($osm['found']) {
            $props['external_verified_osm'] = true;
        } else {
            $props['external_verified_osm'] = false;
        }

        $feature->update(['properties' => $props]);

        // إعادة حساب درجة الثقة بعد تحديث بيانات الفحص الخارجي
        ConfidenceEngine::updateFeatureScore($this->featureId);
    }
}
