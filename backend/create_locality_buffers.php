#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Create buffer polygons for localities
// This converts point geometries to approximate polygon representations

echo "=== Creating buffer polygons for localities ===\n\n";

// Get all localities with points
$localities = DB::table('geo_features')
    ->select('geo_features.id', 'geo_features.geom', 
             DB::raw("properties ->> 'name' as name"),
             'parent_id')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->whereNotNull('geom')
    ->get();

echo "Found {$localities->count()} localities with point geometries.\n";

// Create buffer polygons
$created = 0;
foreach ($localities as $locality) {
    // Create a buffer around the point (in degrees - approximately 5-10 km)
    // Buffer size depends on the state:
    // - Khartoum: smaller buffer (5km) since it's a dense urban area
    // - Gezira: larger buffer (8km) for rural areas
    // - Gedaref: medium buffer (6km)
    
    $stateId = $locality->parent_id;
    $state = DB::table('geo_features')->where('id', $stateId)->first();
    $stateProps = json_decode($state->properties ?? '{}', true);
    $stateName = $stateProps['name'] ?? '';
    
    // Determine buffer size based on state
    $bufferSize = match($stateName) {
        'الخرطوم' => 0.05,   // ~5km
        'الجزيرة' => 0.08,   // ~8km
        'القضارف' => 0.06,   // ~6km
        default => 0.05
    };
    
    // Create buffer polygon using PostGIS
    $result = DB::selectOne("
        SELECT ST_AsText(ST_Buffer(?, ?)) as buffer_wkt
    ", [$locality->geom, $bufferSize]);
    
    if ($result && $result->buffer_wkt) {
        // Update the locality with the buffer polygon
        DB::table('geo_features')
            ->where('id', $locality->id)
            ->update([
                'geom' => DB::raw("ST_GeomFromText('{$result->buffer_wkt}', 4326)")
            ]);
        
        $created++;
    }
}

echo "\n=== Results ===\n";
echo "Created $created locality buffer polygons\n";

// Verify
echo "\n=== Verification ===\n";
$verified = DB::select("
    SELECT 
        ST_GeometryType(geom) as geom_type,
        COUNT(*) as count
    FROM geo_features 
    WHERE layer_id = (SELECT id FROM map_layers WHERE name = 'المحليات')
    GROUP BY ST_GeometryType(geom)
");

foreach ($verified as $row) {
    echo "{$row->geom_type}: {$row->count}\n";
}

echo "\n=== Sample Localities ===\n";
$samples = DB::table('geo_features')
    ->select(DB::raw("properties ->> 'name' as name"), 
             DB::raw("ST_AsText(geom) as geom_wkt"))
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->limit(5)
    ->get();

foreach ($samples as $sample) {
    $geomPreview = strlen($sample->geom_wkt) > 100 
        ? substr($sample->geom_wkt, 0, 100) . "..."
        : $sample->geom_wkt;
    echo "{$sample->name}: {$geomPreview}\n";
}