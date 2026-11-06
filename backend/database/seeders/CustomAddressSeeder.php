<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Addressing\Models\Address;
use App\Domains\Spatial\Services\S2GeometryService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class CustomAddressSeeder extends Seeder
{
    public function run(): void
    {
        $s2Service = app(S2GeometryService::class);
        $jsonPath = database_path('data/custom_addresses.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("GeoJSON file not found at: $jsonPath");
            return;
        }

        $geoJson = json_decode(File::get($jsonPath), true);
        
        if (!isset($geoJson['features'])) {
            $this->command->error("Invalid GeoJSON: 'features' key missing.");
            return;
        }

        foreach ($geoJson['features'] as $feature) {
            $coords = $feature['geometry']['coordinates'];
            $lng = (float) $coords[0];
            $lat = (float) $coords[1];
            $props = $feature['properties'];

            // Generate S2 Token
            $s2Token = $s2Service->latLngToToken($lat, $lng);
            
            // Generate Plus Code
            $plusCode = $s2Service->generatePlusCode($lat, $lng);

            Address::updateOrCreate(
                ['s2_cell_id' => $s2Token],
                [
                    'address_str' => $props['address_str'] ?? ($props['name'] ?? 'عنوان غير مسمى'),
                    'neighborhood' => $props['neighborhood'] ?? 'غير محدد',
                    'plus_code' => $plusCode,
                    'type' => $props['type'] ?? 'residential',
                    'location' => DB::raw("ST_GeomFromText('POINT($lng $lat)', 4326)"),
                    'is_verified' => true,
                    'metadata' => $props // Store all properties from ArcGIS as metadata
                ]
            );
        }

        $this->command->info("Successfully imported " . count($geoJson['features']) . " features from GeoJSON.");
    }
}
