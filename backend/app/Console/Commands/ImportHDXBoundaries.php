<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;

class ImportHDXBoundaries extends Command
{
    protected $signature = 'map:import-hdx {--dir=} {--dry-run}';

    protected $description = 'Import Sudan admin boundaries from HDX SHP directory';

    public function handle()
    {
        $dir = $this->option('dir') ?: 'C:\Users\abubakr\Downloads\sdn_admin_boundaries.shp';
        $dryRun = $this->option('dry-run');

        if (!is_dir($dir)) {
            $this->error("Directory not found: $dir");
            return;
        }

        $admin1 = "$dir/sdn_admin1.shp";
        $admin2 = "$dir/sdn_admin2.shp";

        if (!file_exists($admin1)) {
            $this->error("admin1 SHP not found: $admin1");
            return;
        }
        if (!file_exists($admin2)) {
            $this->error("admin2 SHP not found: $admin2");
            return;
        }

        if ($dryRun) {
            $this->info("DRY RUN - inspecting only");
        }

        $this->info("Importing HDX Sudan admin boundaries...");
        $this->info("  admin1 (States): $admin1");
        $this->info("  admin2 (Localities): $admin2");

        $tempDir = base_path('storage/app/admin-boundaries/hdx_temp');
        if (is_dir($tempDir)) {
            $this->rrmdir($tempDir);
        }
        mkdir($tempDir, 0755, true);

        $this->newLine();
        $this->info("=== Importing States (الولايات) ===");
        $this->importLayer($admin1, 'states', $tempDir, $dryRun);

        $this->newLine();
        $this->info("=== Importing Localities (المحليات) ===");
        $this->importLayer($admin2, 'localities', $tempDir, $dryRun);

        $this->rrmdir($tempDir);

        $this->newLine();
        $this->info("HDX import complete!");
    }

    private function importLayer($shpPath, $type, $tempDir, $dryRun)
    {
        $geojsonPath = "$tempDir/" . basename($shpPath) . ".geojson";

        $this->info("Converting $shpPath to GeoJSON...");
        $pyScript = "
import json
import fiona
from shapely.geometry import shape

shp = r'$shpPath'
out = r'$geojsonPath'

with fiona.open(shp, encoding='utf-8') as src:
    features = []
    for i, f in enumerate(src):
        geom = shape(f['geometry'])
        geojson = geom.__geo_interface__
        props = {}
        for k, v in f['properties'].items():
            if v is not None:
                props[str(k)] = v

        features.append({
            'type': 'Feature',
            'id': i,
            'geometry': geojson,
            'properties': props
        })

with open(out, 'w', encoding='utf-8') as fp:
    json.dump({'type': 'FeatureCollection', 'features': features}, fp, ensure_ascii=False, indent=2)

print(f'DONE:{len(features)}')
";
        $pyFile = "$tempDir/convert.py";
        file_put_contents($pyFile, $pyScript);

        $output = [];
        exec("python \"$pyFile\" 2>&1", $output, $code);

        if ($code !== 0 || !file_exists($geojsonPath)) {
            $this->error("Conversion failed:");
            foreach ($output as $line) {
                $this->line("  " . $line);
            }
            return;
        }

        $jsonStr = file_get_contents($geojsonPath);
        $data = json_decode($jsonStr, true);

        if (!$data || !isset($data['features'])) {
            $this->error("Invalid GeoJSON output");
            return;
        }

        $this->info("Converted " . count($data['features']) . " features");

        foreach ($data['features'] as $i => $feat) {
            $p = $feat['properties'] ?? [];

            if ($type === 'states') {
                $name = $p['adm1_name1'] ?? $p['adm1_name'] ?? 'Unknown';
                $engName = $p['adm1_name'] ?? '';
                $pcode = $p['adm1_pcode'] ?? null;
                $parentPcode = 'SD';
                $geom = $feat['geometry'] ?? null;

                $this->line(sprintf(
                    "  [%2d] %-20s | %-20s | %s",
                    $i + 1,
                    $this->truncate($name, 20),
                    $this->truncate($engName, 20),
                    $pcode ?? 'no-pcode'
                ));
            } else {
                $name = $p['adm2_name1'] ?? $p['adm2_name'] ?? 'Unknown';
                $engName = $p['adm2_name'] ?? '';
                $pcode = $p['adm2_pcode'] ?? null;
                $parentName = $p['adm1_name1'] ?? $p['adm1_name'] ?? 'Unknown';
                $parentPcode = $p['adm1_pcode'] ?? null;
                $geom = $feat['geometry'] ?? null;

                $this->line(sprintf(
                    "  [%3d] %-20s | %-20s | %s | parent: %s",
                    $i + 1,
                    $this->truncate($name, 20),
                    $this->truncate($engName, 20),
                    $pcode ?? 'no-pcode',
                    $this->truncate($parentName, 20)
                ));
            }
        }

        if (!$dryRun) {
            $this->importFeatures($data['features'], $type);
        }
    }

    private function importFeatures($features, $type)
    {
        $layer = match ($type) {
            'states' => MapLayer::firstOrCreate(
                ['name' => 'الولايات'],
                ['description' => 'Sudan States from HDX', 'type' => 'boundary', 'is_public' => true]
            ),
            'localities' => MapLayer::firstOrCreate(
                ['name' => 'المحليات'],
                ['description' => 'Sudan Localities from HDX', 'type' => 'boundary', 'is_public' => true]
            ),
            default => null,
        };

        if (!$layer) return;

        $bar = $this->output->createProgressBar(count($features));
        $bar->start();

        $count = 0;
        $skip = 0;

        foreach ($features as $feat) {
            $p = $feat['properties'] ?? [];
            $geom = $feat['geometry'] ?? null;

            if (!$geom) {
                $bar->advance();
                continue;
            }

            if ($type === 'states') {
                $name = $p['adm1_name1'] ?? $p['adm1_name'] ?? null;
                $engName = $p['adm1_name'] ?? '';
                $pcode = $p['adm1_pcode'] ?? null;
            } else {
                $name = $p['adm2_name1'] ?? $p['adm2_name'] ?? null;
                $engName = $p['adm2_name'] ?? '';
                $pcode = $p['adm2_pcode'] ?? null;
            }

            if (!$name) {
                $bar->advance();
                $skip++;
                continue;
            }

            $wkt = $this->geoJSONToWKT($geom);
            if (!$wkt) {
                $bar->advance();
                $skip++;
                continue;
            }

            $existing = null;
            if ($pcode) {
                $existing = GeoFeature::where('layer_id', $layer->id)
                    ->where(function ($q) use ($pcode, $name) {
                        $q->whereJsonContains('properties->pcode', $pcode)
                          ->orWhere('properties->name', $name);
                    })
                    ->first();
            }

            $data = [
                'layer_id' => $layer->id,
                'properties' => array_merge($p, [
                    'name' => $name,
                    'name_en' => $engName,
                    'pcode' => $pcode,
                    'source' => 'HDX_SDN',
                ]),
                'status' => 'active',
            ];

            $wktEscaped = str_replace("'", "''", $wkt);
            $newId = Str::uuid()->toString();
            $layerId = $layer->id;
            $propsJson = json_encode($data['properties']);
            $s2Service = app(\App\Domains\Spatial\Services\S2GeometryService::class);
            $centerLat = $p['center_lat'] ?? null;
            $centerLng = $p['center_lon'] ?? null;
            if (!$centerLat || !$centerLng) {
                $centroid = DB::selectOne("SELECT ST_Y(ST_Centroid(ST_GeomFromText(?, 4326))) as lat, ST_X(ST_Centroid(ST_GeomFromText(?, 4326))) as lng", [$wkt, $wkt]);
                $centerLat = $centroid->lat;
                $centerLng = $centroid->lng;
            }
            $s2Cell = $s2Service->latLngToToken((float) $centerLat, (float) $centerLng);
            $plusCode = $s2Service->generatePlusCode((float) $centerLat, (float) $centerLng);

            $parentId = null;
            if ($type === 'localities') {
                $parentPcode = $p['adm1_pcode'] ?? null;
                if ($parentPcode) {
                    $parentState = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))
                        ->whereJsonContains('properties->pcode', $parentPcode)
                        ->first();
                    if ($parentState) {
                        $parentId = $parentState->id;
                    }
                }
            }

            if ($existing) {
                $existing->update(['properties' => $data['properties'], 'parent_id' => $parentId]);
                DB::update("UPDATE geo_features SET geom = ST_GeomFromText('$wktEscaped', 4326) WHERE id = ?", [$existing->id]);
            } else {
                DB::insert("INSERT INTO geo_features (id, layer_id, parent_id, geom, properties, status, s2_cell_id, plus_code, created_at, updated_at) VALUES (?, ?, ?, ST_GeomFromText(?, 4326), ?::jsonb, ?, ?, ?, now(), now())", [$newId, $layerId, $parentId, $wkt, $propsJson, 'active', $s2Cell, $plusCode]);
            }

            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported $count features, skipped $skip");
    }

    private function geoJSONToWKT($geometry)
    {
        $type = $geometry['type'] ?? null;
        $coords = $geometry['coordinates'] ?? null;
        if (!$type || !$coords) return null;

        return match ($type) {
            'Point' => sprintf("POINT(%F %F)", $coords[0], $coords[1]),
            'LineString' => "LINESTRING(" . implode(',', array_map(fn($c) => sprintf("%F %F", $c[0], $c[1]), $coords)) . ")",
            'Polygon' => "POLYGON(" . implode(',', array_map(fn($ring) => '(' . implode(',', array_map(fn($c) => sprintf("%F %F", $c[0], $c[1]), $ring)) . ')', $coords)) . ")",
            'MultiPolygon' => "MULTIPOLYGON(" . implode(',', array_map(fn($poly) => '(' . implode(',', array_map(fn($ring) => '(' . implode(',', array_map(fn($c) => sprintf("%F %F", $c[0], $c[1]), $ring)) . ')', $poly)) . ')', $coords)) . ")",
            default => null,
        };
    }

    private function truncate($str, $len)
    {
        if (!$str) return '';
        return mb_strlen($str) > $len ? mb_substr($str, 0, $len - 1) . '…' : $str;
    }

    private function rrmdir($dir)
    {
        if (!is_dir($dir)) return;
        foreach (glob("$dir/*") as $file) {
            is_dir($file) ? $this->rrmdir($file) : unlink($file);
        }
        rmdir($dir);
    }
}