<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\Models\GeoFeature;

class ImportSudanAdminBoundaries extends Command
{
    protected $signature = 'map:import-sudan {--source=osm} {--layer=}';

    protected $description = 'Import Sudan administrative boundaries from OSM or local GeoJSON';

    public function handle()
    {
        $source = $this->option('source');
        $layerName = $this->option('layer');

        $this->info("Starting Sudan admin boundaries import from: $source");

        if ($source === 'osm') {
            $this->importFromOSM();
        } elseif ($source === 'geojson') {
            $this->importFromGeoJSON($layerName);
        } else {
            $this->error("Unknown source: $source. Use --source=osm or --source=geojson");
        }
    }

    private function importFromOSM()
    {
        $this->info('Fetching Sudan states from OSM Overpass API...');

        // Get Sudan admin level 1 (states)
        $queryStates = "[out:json][timeout:300];\n" .
            "relation['admin_level'='4']['name'~'.ال].(area:360600000);\n" .
            "out body;\n" .
            ";>;out skel qt;";

        $queryLocalities = "[out:json][timeout:300];\n" .
            "relation['admin_level'='6']['name'~'.ال].(area:360600000);\n" .
            "out body;\n" .
            ";>;out skel qt;";

        // First, ensure map layers exist
        $statesLayer = MapLayer::firstOrCreate(
            ['name' => 'الولايات'],
            ['description' => 'Sudan Administrative States (OSM)', 'type' => 'boundary', 'is_public' => true]
        );

        $localitiesLayer = MapLayer::firstOrCreate(
            ['name' => 'المحليات'],
            ['description' => 'Sudan Administrative Localities (OSM)', 'type' => 'boundary', 'is_public' => true]
        );

        $this->info("States layer ID: {$statesLayer->id}");
        $this->info("Localities layer ID: {$localitiesLayer->id}");

        // Import states (admin_level = 4)
        $this->importOSMBoundaries($queryStates, $statesLayer, 'الولايات');

        // Import localities (admin_level = 6)
        $this->importOSMBoundaries($queryLocalities, $localitiesLayer, 'المحليات');

        $this->newLine();
        $this->info('Import completed!');
    }

    private function importOSMBoundaries($query, $layer, $layerName)
    {
        $this->info("Fetching $layerName from OSM...");

        try {
            $response = Http::timeout(300)->withHeaders([
                'User-Agent' => 'NamerhaSah/1.0 (admin-boundary-import)',
            ])->post('https://overpass-api.de/api/interpreter', [
                'data' => $query
            ]);

            if ($response->failed()) {
                $this->error("Overpass API failed: " . $response->status());
                return;
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];

            $this->info("Found " . count($elements) . " OSM elements");

            $count = 0;
            foreach ($elements as $element) {
                if ($element['type'] === 'relation' && isset($element['tags']['name'])) {
                    $wkt = $this->relationToWkt($element);
                    if ($wkt) {
                        $this->createOrUpdateGeoFeature($layer, $element, $wkt, $layerName);
                        $count++;
                    }
                } elseif ($element['type'] === 'way' && isset($element['tags']['name'])) {
                    $wkt = $this->wayToWkt($element);
                    if ($wkt) {
                        $this->createOrUpdateGeoFeature($layer, $element, $wkt, $layerName);
                        $count++;
                    }
                }
            }

            $this->info("Imported $count $layerName boundaries");

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("OSM Boundary Import Error: " . $e->getMessage());
        }
    }

    private function relationToWkt($relation)
    {
        if (!isset($relation['members'])) {
            return null;
        }

        $allPoints = [];
        $closedRings = [];

        foreach ($relation['members'] as $member) {
            if (isset($member['geometry']) && count($member['geometry']) > 0) {
                $ring = [];
                foreach ($member['geometry'] as $pt) {
                    $ring[] = sprintf("%F %F", $pt['lon'], $pt['lat']);
                }

                if (count($ring) >= 3) {
                    $closedRings[] = '(' . implode(',', $ring) . ')';
                }
            }
        }

        if (empty($closedRings)) {
            return null;
        }

        if (count($closedRings) === 1) {
            return "POLYGON({$closedRings[0]})";
        }

        return "MULTIPOLYGON(" . implode(',', $closedRings) . ")";
    }

    private function wayToWkt($way)
    {
        if (!isset($way['geometry']) || count($way['geometry']) < 3) {
            return null;
        }

        $points = [];
        foreach ($way['geometry'] as $pt) {
            $points[] = sprintf("%F %F", $pt['lon'], $pt['lat']);
        }

        $first = $points[0];
        $last = end($points);

        if ($first === $last) {
            return "POLYGON((" . implode(',', $points) . "))";
        }

        return "LINESTRING(" . implode(',', $points) . ")";
    }

    private function createOrUpdateGeoFeature($layer, $element, $wkt, $layerName)
    {
        $osmId = $element['id'] ?? null;
        $name = $element['tags']['name'] ?? 'Unknown';
        $parentName = null;

        // Determine parent locality for states
        if ($layerName === 'المحليات' && isset($element['tags']['parent'])) {
            $parentName = $element['tags']['parent'];
        }

        // Check if already exists
        $existing = GeoFeature::where('layer_id', $layer->id)
            ->whereJsonContains('properties->osm_id', $osmId)
            ->first();

        $data = [
            'layer_id' => $layer->id,
            'geometry_wkt' => $wkt,
            'properties' => [
                'osm_id' => $osmId,
                'name' => $name,
                'tags' => $element['tags'] ?? [],
            ],
            'status' => 'active',
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            GeoFeature::create($data);
        }
    }

    private function importFromGeoJSON($layerName = null)
    {
        $this->info('Importing from local GeoJSON files...');

        // Check for admin boundaries directory
        $basePath = base_path('storage/app/admin-boundaries');
        if (!is_dir($basePath)) {
            $this->warn("Directory not found: $basePath");
            $this->info("Please place your GeoJSON files in: storage/app/admin-boundaries/");
            $this->info("Expected files: states.geojson, localities.geojson, neighborhoods.geojson");
            return;
        }

        // Ensure map layers exist
        $statesLayer = MapLayer::firstOrCreate(
            ['name' => 'الولايات'],
            ['description' => 'Sudan States', 'type' => 'boundary', 'is_public' => true]
        );

        $localitiesLayer = MapLayer::firstOrCreate(
            ['name' => 'المحليات'],
            ['description' => 'Sudan Localities', 'type' => 'boundary', 'is_public' => true]
        );

        $neighborhoodsLayer = MapLayer::firstOrCreate(
            ['name' => 'الأحياء'],
            ['description' => 'Sudan Neighborhoods', 'type' => 'boundary', 'is_public' => true]
        );

        $layers = [
            'states' => $statesLayer,
            'localities' => $localitiesLayer,
            'neighborhoods' => $neighborhoodsLayer,
        ];

        foreach ($layers as $name => $layer) {
            if ($layerName && $layerName !== $name) {
                continue;
            }

            $filePath = "$basePath/$name.geojson";
            if (!file_exists($filePath)) {
                $this->warn("File not found: $filePath");
                continue;
            }

            $this->info("Importing $name from $filePath...");
            $this->importGeoJSONFile($filePath, $layer, $name);
        }

        $this->newLine();
        $this->info('GeoJSON import completed!');
    }

    private function importGeoJSONFile($filePath, $layer, $layerName)
    {
        $content = file_get_contents($filePath);
        $geojson = json_decode($content, true);

        if (!$geojson || !isset($geojson['features'])) {
            $this->error("Invalid GeoJSON file: $filePath");
            return;
        }

        $count = 0;
        $features = $geojson['features'];

        $this->output->progressStart(count($features));

        foreach ($features as $feature) {
            $props = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? null;

            if (!$geometry) {
                $this->output->progressAdvance();
                continue;
            }

            $name = $props['name'] ?? $props['NAME_1'] ?? $props['admin_name'] ?? 'Unknown';
            $osmId = $props['osm_id'] ?? $props['id'] ?? null;

            // Convert GeoJSON to WKT
            $wkt = $this->geoJSONToWKT($geometry);

            if (!$wkt) {
                $this->output->progressAdvance();
                continue;
            }

            $existing = GeoFeature::where('layer_id', $layer->id)
                ->where(function ($q) use ($osmId, $name) {
                    if ($osmId) {
                        $q->whereJsonContains('properties->osm_id', $osmId);
                    } else {
                        $q->where('properties->name', $name);
                    }
                })
                ->first();

            $data = [
                'layer_id' => $layer->id,
                'geometry_wkt' => $wkt,
                'properties' => array_merge($props, ['name' => $name]),
                'status' => 'active',
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                GeoFeature::create($data);
            }

            $count++;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("Imported $count $layerName features");
    }

    private function geoJSONToWKT($geometry)
    {
        $type = $geometry['type'] ?? null;
        $coords = $geometry['coordinates'] ?? null;

        if (!$type || !$coords) {
            return null;
        }

        switch ($type) {
            case 'Point':
                return sprintf("POINT(%F %F)", $coords[0], $coords[1]);

            case 'LineString':
                $points = array_map(fn($c) => sprintf("%F %F", $c[0], $c[1]), $coords);
                return "LINESTRING(" . implode(',', $points) . ")";

            case 'Polygon':
                $rings = array_map(function ($ring) {
                    $points = array_map(fn($c) => sprintf("%F %F", $c[0], $c[1]), $ring);
                    return '(' . implode(',', $points) . ')';
                }, $coords);
                return "POLYGON(" . implode(',', $rings) . ")";

            case 'MultiPoint':
                $points = array_map(fn($c) => sprintf("(%F %F)", $c[0], $c[1]), $coords);
                return "MULTIPOINT(" . implode(',', $points) . ")";

            case 'MultiLineString':
                $lines = array_map(function ($line) {
                    $points = array_map(fn($c) => sprintf("%F %F", $c[0], $c[1]), $line);
                    return '(' . implode(',', $points) . ')';
                }, $coords);
                return "MULTILINESTRING(" . implode(',', $lines) . ")";

            case 'MultiPolygon':
                $polygons = array_map(function ($polygon) {
                    $rings = array_map(function ($ring) {
                        $points = array_map(fn($c) => sprintf("%F %F", $c[0], $c[1]), $ring);
                        return '(' . implode(',', $points) . ')';
                    }, $polygon);
                    return '(' . implode(',', $rings) . ')';
                }, $coords);
                return "MULTIPOLYGON(" . implode(',', $polygons) . ")";

            default:
                return null;
        }
    }
}