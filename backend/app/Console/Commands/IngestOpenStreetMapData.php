<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\Models\GeoFeature;
use Illuminate\Support\Facades\Log;

class IngestOpenStreetMapData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'map:ingest-osm {tag : The OSM tag in key=value format (e.g. amenity=hospital)} {--bbox=15.4,32.4,15.7,32.7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingests point of interest data from OpenStreetMap via Overpass API into GeoFeatures.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tag = $this->argument('tag');
        $inputBbox = $this->option('bbox');

        $regions = [
            'khartoum' => '15.4,32.4,15.7,32.7',
            'gezira' => '14.2,33.2,14.6,33.7',
            'gezira_state' => '13.5,32.3,15.6,34.2',
            'gedaref' => '13.9,35.2,14.2,35.6',
        ];

        $bbox = $regions[strtolower($inputBbox)] ?? $inputBbox;

        // Parse key=value
        if (!str_contains($tag, '=')) {
            $this->error("Tag must be in key=value format (e.g., amenity=hospital)");
            return;
        }

        list($key, $val) = explode('=', $tag, 2);

        $this->info("Fetching [$key=$val] from OSM in Bounding Box: [$bbox]...");

        // Construct Overpass QL
        // Using 'out geom' to get the full path for ways and relations
        $query = "[out:json][timeout:180];\n( nwr[\"{$key}\"=\"{$val}\"]({$bbox}); );\nout geom qt;";

        try {
            $response = Http::timeout(180)->withHeaders([
                'User-Agent' => 'NamerhaSah/1.0',
                'Accept' => 'application/json'
            ])->asForm()->post('https://overpass-api.de/api/interpreter', [
                'data' => $query
            ]);

            if ($response->failed()) {
                $this->error("Failed to connect to Overpass API. Status: " . $response->status());
                return;
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];

            if (count($elements) === 0) {
                $this->warn("No data found for tag: $tag");
                return;
            }

            $this->info("Found " . count($elements) . " elements. Creating Layer if not exists...");

            $layer = MapLayer::firstOrCreate(
                ['name' => $this->formatLayerName($val)],
                ['description' => "OSM $val layer", 'type' => 'mixed', 'is_public' => true]
            );

            $this->withProgressBar($elements, function ($node) use ($layer) {
                $wkt = $this->elementToWkt($node);
                
                if ($wkt) {
                    $existing = GeoFeature::where('layer_id', $layer->id)
                        ->whereJsonContains('properties->osm_id', $node['id'])
                        ->first();

                    if (!$existing) {
                        GeoFeature::create([
                            'layer_id' => $layer->id,
                            'geometry_wkt' => $wkt,
                            'properties' => [
                                'osm_id' => $node['id'],
                                'name' => $node['tags']['name'] ?? 'Unknown',
                                'type' => $node['type'],
                                'tags' => $node['tags'] ?? []
                            ],
                            'status' => 'verified'
                        ]);
                    }
                }
            });

            $this->newLine();
            $this->info("Ingestion completed successfully!");

        } catch (Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            Log::error("OSM Ingestion Error: " . $e->getMessage());
        }
    }

    private function formatLayerName($val)
    {
        return ucfirst(str_replace('_', ' ', $val));
    }

    private function elementToWkt($element)
    {
        // 1. Handle Nodes
        if ($element['type'] === 'node' && isset($element['lat'])) {
            return sprintf("POINT(%F %F)", $element['lon'], $element['lat']);
        }
        
        // 2. Handle Ways (Direct geometry)
        if ($element['type'] === 'way' && isset($element['geometry']) && count($element['geometry']) > 0) {
            $points = [];
            foreach ($element['geometry'] as $pt) {
                $points[] = sprintf("%F %F", $pt['lon'], $pt['lat']);
            }
            
            $wktPoints = implode(',', $points);
            
            // If the way is closed, represent it as a POLYGON
            if ($points[0] === end($points) && count($points) >= 4) {
                return "POLYGON(($wktPoints))";
            }
            return "LINESTRING($wktPoints)";
        }

        // 3. Handle Relations (Joined member geometries)
        if ($element['type'] === 'relation' && isset($element['members'])) {
            $allPoints = [];
            foreach ($element['members'] as $member) {
                if (isset($member['geometry'])) {
                    foreach ($member['geometry'] as $pt) {
                        $allPoints[] = sprintf("%F %F", $pt['lon'], $pt['lat']);
                    }
                }
            }
            
            if (count($allPoints) > 0) {
                $wktPoints = implode(',', $allPoints);
                // Simplified: Treat all relation geometries as a single LineString/Polygon for now
                if ($allPoints[0] === end($allPoints) && count($allPoints) >= 4) {
                    return "POLYGON(($wktPoints))";
                }
                return "LINESTRING($wktPoints)";
            }
        }

        // 4. Fallback to center point if available (as a POINT)
        if (isset($element['center'])) {
            return sprintf("POINT(%F %F)", $element['center']['lon'], $element['center']['lat']);
        }

        return null;
    }
}
