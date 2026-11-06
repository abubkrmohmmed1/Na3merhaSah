<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\Models\GeoFeature;
use ZipArchive;

class ImportLocalitiesBoundaries extends Command
{
    protected $signature = 'map:import-localities {--file=}';

    protected $description = 'Import/update Sudan localities boundaries from SHP or GeoJSON file';

    public function handle()
    {
        $filePath = $this->option('file');

        if (!$filePath) {
            $defaultPath = base_path('storage/app/admin-boundaries/sdn_admin_boundaries.shp.zip');
            if (file_exists($defaultPath)) {
                $filePath = $defaultPath;
            } else {
                $defaultGeoJSON = base_path('storage/app/admin-boundaries/localities.geojson');
                if (file_exists($defaultGeoJSON)) {
                    $filePath = $defaultGeoJSON;
                }
            }
        }

        if (!$filePath || !file_exists($filePath)) {
            $this->error("File not found. Please provide --file=/path/to/file");
            $this->info("Supported formats:");
            $this->line("  - .shp.zip (HDX Sudan admin boundaries)");
            $this->line("  - .geojson");
            $this->line("");
            $this->line("Place your files in: " . base_path('storage/app/admin-boundaries'));
            return;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'zip') {
            $this->importFromZip($filePath);
        } elseif ($ext === 'geojson' || $ext === 'json') {
            $this->importFromGeoJSON($filePath);
        } else {
            $this->error("Unsupported file format: $ext");
        }
    }

    private function importFromZip($zipPath)
    {
        $this->info("Reading ZIP file: $zipPath");

        $extractDir = base_path('storage/app/admin-boundaries/extracted');

        if (!is_dir($extractDir)) {
            mkdir($extractDir, 0755, true);
        }

        // Extract ZIP
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->error("Cannot open ZIP file");
            return;
        }

        // List files in ZIP
        $shpFile = null;
        $dbfFile = null;
        $geojsonFile = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $basename = strtolower(basename($name));

            if (str_ends_with($basename, '.shp')) {
                $shpFile = $name;
            } elseif (str_ends_with($basename, '.dbf')) {
                $dbfFile = $name;
            } elseif (str_ends_with($basename, '.geojson') || str_ends_with($basename, '.json')) {
                $geojsonFile = $name;
            }
        }

        // Prefer GeoJSON if available
        if ($geojsonFile) {
            $tempGeoJSON = $extractDir . '/temp.geojson';
            copy("zip://$zipPath#{$geojsonFile}", $tempGeoJSON);
            $zip->close();
            $this->importFromGeoJSON($tempGeoJSON);
            return;
        }

        // Extract SHP + DBF for manual parsing
        $extractFiles = [];
        foreach (['shp', 'dbf', 'shx', 'cpg'] as $ext2) {
            $varName = $ext2 . 'File';
            if ($$varName) {
                $outPath = $extractDir . '/' . basename($$varName);
                copy("zip://$zipPath#" . $$varName, $outPath);
                $extractFiles[] = $outPath;
            }
        }
        $zip->close();

        if (!$shpFile || !$dbfFile) {
            $this->error("SHP or DBF file not found in ZIP");
            return;
        }

        $this->info("SHP: " . basename($shpFile));
        $this->info("DBF: " . basename($dbfFile));

        // Parse SHP + DBF
        $this->parseSHP($extractDir);
    }

    private function parseSHP($dir)
    {
        $shpFiles = glob("$dir/*.shp");
        if (empty($shpFiles)) {
            $this->error("No SHP file found after extraction");
            return;
        }

        $shpPath = $shpFiles[0];
        $dbfPath = str_replace('.shp', '.dbf', $shpPath);

        if (!file_exists($dbfPath)) {
            $this->error("DBF file not found: $dbfPath");
            return;
        }

        $this->info("Parsing SHP file...");

        // Read SHP header
        $shp = fopen($shpPath, 'rb');
        $header = unpack('VfileCode/Vunused1/Vunused2/Vunused3/Vunused4/Vunused5/Vunused6/Vunused7/Vunused8/Vunused9/Vunused10/Vlength', fread($shp, 36));

        fseek($shp, 100); // Skip to first record

        // Read DBF
        $dbf = fopen($dbfPath, 'rb');
        $dbfHeader = fread($dbf, 32);
        $numRecords = unpack('V', fread($dbf, 4));
        $numRecords = $numRecords[1];
        $headerLen = unpack('v', fread($dbf, 2));
        $headerLen = $headerLen[1];
        $recordLen = unpack('v', fread($dbf, 2));
        $recordLen = $recordLen[1];

        fseek($dbf, $headerLen);

        // Get field names from DBF header
        $fields = [];
        while (true) {
            $fieldHeader = fread($dbf, 32);
            if (ord($fieldHeader[0]) === 0x0D) break;

            $fieldName = trim(substr($fieldHeader, 0, 11));
            $fieldType = ord($fieldHeader[11]);
            $fieldLen = ord($fieldHeader[16]);

            $fields[] = ['name' => $fieldName, 'type' => $fieldType, 'len' => $fieldLen];
        }

        // Read first record (skip delete flag)
        fseek($dbf, $headerLen + 1);

        $this->info("DBF Records: $numRecords");
        $this->info("DBF Fields: " . implode(', ', array_column($fields, 'name')));

        // Find relevant columns
        $nameCol = null;
        $adminLvlCol = null;
        $parentCol = null;
        foreach ($fields as $idx => $f) {
            $name = strtolower($f['name']);
            if (in_array($name, ['name', 'admin_name', 'adm1_en', 'adm2_en'])) $nameCol = $idx;
            if (in_array($name, ['admin_level', 'adm_level'])) $adminLvlCol = $idx;
            if (in_array($name, ['parent', 'parent_id', 'admin_ref'])) $parentCol = $idx;
        }

        // Create/ensure map layer
        $layer = MapLayer::firstOrCreate(
            ['name' => 'المحليات'],
            ['description' => 'Sudan Administrative Localities', 'type' => 'boundary', 'is_public' => true]
        );

        $this->info("Layer ID: {$layer->id}");

        $count = 0;
        $this->output->progressStart($numRecords);

        for ($r = 0; $r < $numRecords; $r++) {
            fseek($dbf, $headerLen + 1 + ($r * $recordLen));

            // Read delete flag
            $deleteFlag = fread($dbf, 1);
            if (ord($deleteFlag) === 0x2A) {
                $this->output->progressAdvance();
                continue; // Deleted record
            }

            // Read field values
            $values = [];
            foreach ($fields as $f) {
                $val = trim(fread($dbf, $f['len']));
                $values[] = $val;
            }

            $name = $nameCol !== null ? ($values[$nameCol] ?? '') : '';
            $adminLevel = $adminLvlCol !== null ? ($values[$adminLvlCol] ?? '') : '';

            if (empty($name) || $adminLevel !== '6') {
                $this->output->progressAdvance();
                continue; // Skip non-localities
            }

            // Parse SHP geometry for this record
            $shapeData = $this->readNextShape($shp);
            if ($shapeData && $shapeData['type'] === 5) { // Polygon
                $wkt = $this->shapeToWKT($shapeData);
            } else {
                $wkt = null;
            }

            if ($wkt) {
                $osmId = 'sdn_' . md5($name . $r);

                $existing = GeoFeature::where('layer_id', $layer->id)
                    ->whereJsonContains('properties->source_id', $osmId)
                    ->first();

                $data = [
                    'layer_id' => $layer->id,
                    'geometry_wkt' => $wkt,
                    'properties' => [
                        'source_id' => $osmId,
                        'name' => $name,
                        'admin_level' => $adminLevel,
                        'source' => 'HDX_SDN_ADMIN',
                    ],
                    'status' => 'active',
                ];

                if ($existing) {
                    $existing->update($data);
                } else {
                    GeoFeature::create($data);
                }
                $count++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        fclose($shp);
        fclose($dbf);

        $this->newLine();
        $this->info("✅ Imported $count localities boundaries");

        // Cleanup
        $this->info("Cleaning up extracted files...");
        array_map('unlink', glob("$dir/*"));
        rmdir($dir);
    }

    private function readNextShape($shp)
    {
        if (feof($shp)) return null;

        $header = fread($shp, 8);
        if (strlen($header) < 8) return null;

        $recHeader = unpack('VcontentLen/VshapeType', $header);
        $contentLen = $recHeader['contentLen'];
        $shapeType = $recHeader['shapeType'];

        $data = fread($shp, $contentLen * 2 - 8);

        // MultiPolygon (type 5)
        if ($shapeType === 5) {
            return [
                'type' => 5,
                'data' => $data,
            ];
        }

        return ['type' => $shapeType, 'data' => null];
    }

    private function shapeToWKT($shape)
    {
        if ($shape['type'] !== 5) return null;

        $data = $shape['data'];
        if (strlen($data) < 44) return null;

        // Skip: minX(8), minY(8), maxX(8), maxY(8), numParts(4), numPoints(4) = 40 bytes header
        $pos = 40;

        $numParts = unpack('V', substr($data, 32, 4));
        $numParts = $numParts[1];
        $numPoints = unpack('V', substr($data, 36, 4));
        $numPoints = $numPoints[1];

        $parts = [];
        for ($i = 0; $i < $numParts; $i++) {
            $parts[] = unpack('V', substr($data, $i * 4, 4))[1];
        }
        $parts[] = $numPoints;

        $coordinates = [];
        for ($p = 0; $p < $numPoints; $p++) {
            $x = unpack('d', substr($data, $pos, 8));
            $y = unpack('d', substr($data, $pos + 8, 8));
            $pos += 16;
            $coordinates[] = sprintf("%F %F", $x[1], $y[1]);
        }

        $rings = [];
        for ($i = 0; $i < $numParts; $i++) {
            $start = $parts[$i];
            $end = $parts[$i + 1];
            $ring = array_slice($coordinates, $start, $end - $start);
            if (count($ring) >= 3) {
                $rings[] = '(' . implode(',', $ring) . ')';
            }
        }

        if (count($rings) === 0) return null;
        if (count($rings) === 1) return "POLYGON({$rings[0]})";
        return "MULTIPOLYGON(" . implode(',', $rings) . ")";
    }

    private function importFromGeoJSON($filePath)
    {
        $this->info("Reading GeoJSON: $filePath");

        $content = file_get_contents($filePath);
        $geojson = json_decode($content, true);

        if (!$geojson || !isset($geojson['features'])) {
            $this->error("Invalid GeoJSON file");
            return;
        }

        $this->info("Features: " . count($geojson['features']));

        // Print first feature props for inspection
        if (!empty($geojson['features'])) {
            $first = $geojson['features'][0];
            $this->info("First feature properties: " . json_encode($first['properties'] ?? []));
        }

        // Determine layer based on content
        $layer = MapLayer::firstOrCreate(
            ['name' => 'المحليات'],
            ['description' => 'Sudan Administrative Localities', 'type' => 'boundary', 'is_public' => true]
        );

        $this->info("Layer ID: {$layer->id}");

        // Count existing
        $existing = GeoFeature::where('layer_id', $layer->id)->count();
        $this->info("Existing localities: $existing");

        $count = 0;
        $this->output->progressStart(count($geojson['features']));

        foreach ($geojson['features'] as $feature) {
            $props = $feature['properties'] ?? [];
            $geom = $feature['geometry'] ?? null;

            if (!$geom) {
                $this->output->progressAdvance();
                continue;
            }

            // Determine name field
            $name = $props['name'] ?? $props['ADM2_EN'] ?? $props['ADM2_PCODE'] ?? $props['admin_name'] ?? 'Unknown';

            // Determine admin level
            $adminLevel = $props['admin_level'] ?? $props['ADM2_PCODE'] ?? null;
            $isLocality = ($adminLevel == 2 || str_contains(($adminLevel ?? ''), '2'));

            // Skip if not locality
            if (!$isLocality && count($geojson['features']) > 100) {
                // Maybe this is states file
            }

            $wkt = $this->geoJSONToWKT($geom);
            if (!$wkt) {
                $this->output->progressAdvance();
                continue;
            }

            $sourceId = $props['osm_id'] ?? $props['pcode'] ?? $props['ADM2_PCODE'] ?? md5(json_encode($props));

            // Check if existing
            $existingFeature = GeoFeature::where('layer_id', $layer->id)
                ->where(function ($q) use ($sourceId, $name) {
                    $q->whereJsonContains('properties->source_id', $sourceId)
                      ->orWhere('properties->name', $name);
                })
                ->first();

            $data = [
                'layer_id' => $layer->id,
                'geometry_wkt' => $wkt,
                'properties' => array_merge($props, [
                    'name' => $name,
                    'source_id' => $sourceId,
                    'source' => 'HDX_GEOJSON',
                ]),
                'status' => 'active',
            ];

            if ($existingFeature) {
                $existingFeature->update($data);
            } else {
                GeoFeature::create($data);
            }

            $count++;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine();
        $this->info("✅ Imported/updated $count localities boundaries");
    }

    private function geoJSONToWKT($geometry)
    {
        $type = $geometry['type'] ?? null;
        $coords = $geometry['coordinates'] ?? null;

        if (!$type || !$coords) return null;

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