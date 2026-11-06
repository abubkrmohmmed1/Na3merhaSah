<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\Models\GeoFeature;

class ImportNeighborhoodsFromOSM extends Command
{
    protected $signature = 'map:import-neighborhoods {--state=}';

    protected $description = 'Import neighborhoods (أحياء) from OpenStreetMap for Sudan states';

    public function handle()
    {
        $stateFilter = $this->option('state');

        $this->info('Fetching neighborhoods from OSM Overpass API...');

        // Ensure layer exists
        $layer = MapLayer::firstOrCreate(
            ['name' => 'الأحياء'],
            ['description' => 'Sudan Neighborhoods (OSM)', 'type' => 'boundary', 'is_public' => true]
        );
        $this->info("Layer: {$layer->name} (ID: {$layer->id})");

        // Get states to iterate over
        $states = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))
            ->whereIn('status', ['active', 'pending', 'verified'])
            ->get();

        if ($states->isEmpty()) {
            $this->warn("No states found. Importing all neighborhoods for Sudan...");
            $this->importAllNeighborhoods($layer, $stateFilter);
            return;
        }

        foreach ($states as $state) {
            $stateName = $state->properties['name'] ?? 'Unknown';

            if ($stateFilter && $stateName !== $stateFilter) {
                continue;
            }

            $this->info("Fetching neighborhoods for: $stateName");

            // Get state geometry bounds
            $bounds = $this->getBoundsFromFeature($state);
            if (!$bounds) {
                $this->warn("  Cannot get bounds for $stateName, skipping...");
                continue;
            }

            $this->importNeighborhoodsInBounds($layer, $bounds, $stateName);
        }

        $this->newLine();
        $this->info('✅ Import completed!');
    }

    private function importAllNeighborhoods($layer, $stateFilter = null)
    {
        // Sudan bounding box: south,west,north,east
        $bbox = '8.0,22.0,22.0,38.0';

        $query = "[out:json][timeout:300][bbox:$bbox];
relation['admin_level'='8']['name'~'.ال.'];
out body geom;
";

        $this->info("Querying OSM for admin_level=8 (neighborhoods) in Sudan...");
        $this->processQuery($layer, $query, 'All Sudan Neighborhoods');
    }

    private function importNeighborhoodsInBounds($layer, $bounds, $stateName)
    {
        $bbox = "{$bounds['minLat']},{$bounds['minLon']},{$bounds['maxLat']},{$bounds['maxLon']}";

        $this->info("  BBOX: $bbox");

        $query = "[out:json][timeout:300][bbox:$bbox];
(
  node['place'='village'];
  node['place'='suburb'];
  node['place'='neighbourhood'];
  node['place'='hamlet'];
  node['place'='quarter'];
  node['place'='town'];
  way['place'='village'];
  way['place'='suburb'];
  way['place'='neighbourhood'];
  way['place'='hamlet'];
  way['place'='quarter'];
  relation['admin_level'='8'];
  relation['admin_level'='9'];
  relation['admin_level'='10'];
);
out body geom;
";

        $this->processQuery($layer, $query, $stateName);
    }

    private function processQuery($layer, $query, $context)
    {
        try {
            $response = Http::timeout(300)->asForm()->withHeaders([
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

            $this->info("  Found " . count($elements) . " OSM elements");

            $relations = 0;
            $ways = 0;

            $nodes = 0;

            foreach ($elements as $element) {
                if ($element['type'] === 'relation') {
                    $this->processRelation($layer, $element, $context);
                    $relations++;
                } elseif ($element['type'] === 'way' && isset($element['geometry']) && count($element['geometry']) >= 3) {
                    $this->processWay($layer, $element, $context);
                    $ways++;
                } elseif ($element['type'] === 'node' && isset($element['lat']) && isset($element['lon'])) {
                    $this->processNode($layer, $element, $context);
                    $nodes++;
                }
            }

            $this->info("  Imported: $relations relations, $ways ways, $nodes nodes");

        } catch (\Exception $e) {
            $this->error("  Error: " . $e->getMessage());
            Log::error("OSM Neighborhoods Import Error: " . $e->getMessage());
        }
    }

    private function processRelation($layer, $relation, $stateName)
    {
        $osmId = 'osm_rel_' . $relation['id'];
        $name = $relation['tags']['name'] ?? $relation['tags']['addr:city'] ?? $relation['tags']['place'] ?? null;

        if (!$name) return;

        $wkt = $this->relationToWKT($relation);
        if (!$wkt) return;

        $this->saveGeoFeature($layer, $osmId, $name, $wkt, $relation['tags'], $stateName);
    }

    private function processWay($layer, $way, $stateName)
    {
        $osmId = 'osm_way_' . $way['id'];
        $name = $way['tags']['name'] ?? null;

        if (!$name) return;

        $wkt = $this->wayToWKT($way);
        if (!$wkt) return;

        $this->saveGeoFeature($layer, $osmId, $name, $wkt, $way['tags'], $stateName);
    }

    private function processNode($layer, $node, $stateName)
    {
        $osmId = 'osm_node_' . $node['id'];
        $name = $node['tags']['name'] ?? null;

        if (!$name) return;

        $wkt = sprintf("POINT(%F %F)", $node['lon'], $node['lat']);

        $this->saveGeoFeature($layer, $osmId, $name, $wkt, $node['tags'], $stateName);
    }

    private function saveGeoFeature($layer, $osmId, $name, $wkt, $tags, $stateName)
    {
        $existing = GeoFeature::where('layer_id', $layer->id)
            ->whereJsonContains('properties->osm_id', $osmId)
            ->first();

        $data = [
            'layer_id' => $layer->id,
            'geometry_wkt' => $wkt,
            'properties' => [
                'osm_id' => $osmId,
                'name' => $name,
                'tags' => $tags,
                'state' => $stateName,
                'type' => 'neighborhood',
            ],
            'status' => 'active',
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            GeoFeature::create($data);
        }
    }

    private function relationToWKT($relation)
    {
        if (!isset($relation['members'])) return null;

        $allRings = [];
        $seen = [];

        foreach ($relation['members'] as $member) {
            if (!isset($member['geometry']) || count($member['geometry']) < 3) continue;

            // Build ring string for dedup
            $firstPt = $member['geometry'][0];
            $lastPt = $member['geometry'][count($member['geometry']) - 1];
            $ringKey = "{$firstPt['lat']},{$firstPt['lon']}";

            if (isset($seen[$ringKey])) continue;
            $seen[$ringKey] = true;

            $points = array_map(
                fn($pt) => sprintf("%F %F", $pt['lon'], $pt['lat']),
                $member['geometry']
            );
            // Ensure ring is closed
            if (count($points) > 0 && $points[0] !== end($points)) {
                $points[] = $points[0];
            }
            $allRings[] = '(' . implode(',', $points) . ')';
        }

        if (empty($allRings)) return null;

        if (count($allRings) === 1) {
            return "POLYGON({$allRings[0]})";
        }

        $polygons = array_map(fn($ring) => "($ring)", $allRings);
        return "MULTIPOLYGON(" . implode(',', $polygons) . ")";
    }

    private function wayToWKT($way)
    {
        if (!isset($way['geometry']) || count($way['geometry']) < 3) return null;

        $points = array_map(
            fn($pt) => sprintf("%F %F", $pt['lon'], $pt['lat']),
            $way['geometry']
        );

        $first = $points[0];
        $last = end($points);

        if ($first === $last) {
            return "POLYGON((" . implode(',', $points) . "))";
        }

        return "LINESTRING(" . implode(',', $points) . ")";
    }

    private function getBoundsFromFeature($feature)
    {
        // Get bounds from WKB geometry
        $bbox = DB::selectOne(
            "SELECT ST_XMin(ST_Envelope(geom)) as minx, ST_YMin(ST_Envelope(geom)) as miny,
                    ST_XMax(ST_Envelope(geom)) as maxx, ST_YMax(ST_Envelope(geom)) as maxy
             FROM geo_features WHERE id = ?",
            [$feature->id]
        );

        if (!$bbox) return null;

        return [
            'minLon' => $bbox->minx,
            'minLat' => $bbox->miny,
            'maxLon' => $bbox->maxx,
            'maxLat' => $bbox->maxy,
        ];
    }
}