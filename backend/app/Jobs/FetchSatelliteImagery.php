<?php

namespace App\Jobs;

use App\Domains\Spatial\Models\GeoFeature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FetchSatelliteImagery implements ShouldQueue
{
    use Queueable;

    protected $featureId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $featureId)
    {
        $this->featureId = $featureId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $feature = GeoFeature::find($this->featureId);
        if (!$feature) return;

        // Get Centroid Lat/Lng
        $coords = \Illuminate\Support\Facades\DB::selectOne("
            SELECT ST_Y(ST_Centroid(geom)) as lat, ST_X(ST_Centroid(geom)) as lng 
            FROM geo_features WHERE id = ?
        ", [$this->featureId]);

        if (!$coords) return;

        $apiKey = config('services.google_maps.key');
        if (!$apiKey) return;

        $lat = $coords->lat;
        $lng = $coords->lng;
        
        $url = "https://maps.googleapis.com/maps/api/staticmap?center=$lat,$lng&zoom=19&size=600x600&maptype=satellite&markers=color:red%7C$lat,$lng&key=$apiKey";

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $path = "satellite/{$this->featureId}.jpg";
                Storage::disk('public')->put($path, $response->body());
                
                // Update feature properties to include satellite image path
                $props = $feature->properties ?? [];
                $props['satellite_image'] = $path;
                $feature->update(['properties' => $props]);
            }
        } catch (\Exception $e) {
            Log::error("Satellite Image Fetch Failed: " . $e->getMessage());
        }
    }
}
