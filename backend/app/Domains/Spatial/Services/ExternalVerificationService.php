<?php

namespace App\Domains\Spatial\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalVerificationService
{
    /**
     * Cross-reference a coordinate with OpenStreetMap to find matching features.
     */
    public static function checkOSM(double $lat, double $lng, string $layerName): array
    {
        $radius = 50; // meters
        $overpassUrl = "https://overpass-api.de/api/interpreter";
        
        // Map our internal layers to OSM tags
        $tagMap = [
            'المطاعم' => 'amenity=restaurant',
            'الصيدليات' => 'amenity=pharmacy',
            'المساجد' => 'amenity=place_of_worship',
            'المستشفيات' => 'amenity=hospital',
            'المدارس' => 'amenity=school',
            'محطات الوقود' => 'amenity=fuel',
            'البنوك' => 'amenity=bank',
        ];

        $tag = $tagMap[$layerName] ?? null;
        
        try {
            $query = "[out:json];node(around:$radius,$lat,$lng)";
            if ($tag) {
                $query = "[out:json];node(around:$radius,$lat,$lng)[$tag];";
            } else {
                $query .= ";";
            }
            $query .= "out body;";

            $response = Http::timeout(10)->get($overpassUrl, ['data' => $query]);

            if ($response->successful()) {
                $elements = $response->json()['elements'] ?? [];
                if (count($elements) > 0) {
                    return [
                        'found' => true,
                        'source' => 'OSM',
                        'data' => $elements[0],
                        'confidence_boost' => 40
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("OSM Verification Failed: " . $e->getMessage());
        }

        return ['found' => false];
    }

    /**
     * Check with Google Places API (Requires API Key)
     */
    public static function checkGooglePlaces(double $lat, double $lng, string $name): array
    {
        $apiKey = config('services.google_maps.key');
        if (!$apiKey) return ['found' => false, 'error' => 'API Key missing'];

        try {
            $response = Http::get("https://maps.googleapis.com/maps/api/place/nearbysearch/json", [
                'location' => "$lat,$lng",
                'radius' => 50,
                'keyword' => $name,
                'key' => $apiKey
            ]);

            if ($response->successful()) {
                $results = $response->json()['results'] ?? [];
                if (count($results) > 0) {
                    return [
                        'found' => true,
                        'source' => 'Google',
                        'place_id' => $results[0]['place_id'],
                        'rating' => $results[0]['rating'] ?? null,
                        'confidence_boost' => 50
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Google Verification Failed: " . $e->getMessage());
        }

        return ['found' => false];
    }
}
