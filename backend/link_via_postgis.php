<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// First, let's see what the actual geometry data looks like
$samples = DB::table('geo_features')
    ->select('geo_features.id', 
             DB::raw('ST_AsText(geo_features.geom) as geom_wkt'),
             DB::raw('ST_GeometryType(geo_features.geom) as geom_type'),
             DB::raw('ST_SRID(geo_features.geom) as srid'))
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'الولايات');
    })
    ->limit(3)
    ->get();

echo "State geometry samples:\n";
foreach ($samples as $sample) {
    $wkt = $sample->geom_wkt ?? 'NULL';
    echo "ID: {$sample->id}\n";
    echo "Type: {$sample->geom_type}\n";
    echo "SRID: {$sample->srid}\n";
    echo "WKT (first 200 chars): " . substr($wkt, 0, 200) . ($wkt && strlen($wkt) > 200 ? "..." : "") . "\n\n";
}

// Now let's try to link using PostGIS functions directly
echo "\nAttempting to link localities to states using ST_Contains...\n";

// Reset parent_id for localities
DB::table('geo_features')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->update(['parent_id' => null]);

echo "Reset parent_id for all localities.\n";

// Get states and localities with a spatial join
$linked = DB::table('geo_features as localities')
    ->select('localities.id as locality_id', 
             DB::raw("localities.properties ->> 'name' as locality_name"),
             'states.id as state_id',
             DB::raw("states.properties ->> 'name' as state_name"))
    ->join('geo_features as states', function($join) {
        $join->on(DB::raw('ST_Contains(states.geom, localities.geom)'))
             ->whereExists(function($query) {
                 $query->select(DB::raw(1))
                       ->from('map_layers')
                       ->whereColumn('states.layer_id', 'map_layers.id')
                       ->where('map_layers.name', 'الولايات');
             })
             ->whereExists(function($query) {
                 $query->select(DB::raw(1))
                       ->from('map_layers')
                       ->whereColumn('localities.layer_id', 'map_layers.id')
                       ->where('map_layers.name', 'المحليات');
             });
    })
    ->whereNull('localities.parent_id')
    ->get();

echo "Found " . count($linked) . " locality-state matches via ST_Contains.\n";

foreach ($linked as $link) {
    echo "Link: {$link->locality_name} -> {$link->state_name}\n";
    
    // Update the database
    DB::table('geo_features')
        ->where('id', $link->locality_id)
        ->update(['parent_id' => $link->state_id]);
}

echo "\nUpdated " . count($linked) . " localities with parent_id.\n";

// Show final counts per state
echo "\n=== Final Counts per State ===\n";
$states = DB::table('geo_features')
    ->select('geo_features.id', 
             DB::raw("geo_features.properties ->> 'name' as state_name"))
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'الولايات');
    })
    ->get();

foreach ($states as $state) {
    $count = DB::table('geo_features')
        ->where('parent_id', $state->id)
        ->count();
    
    $stateName = json_decode($state->properties, true)->name ?? 'Unknown';
    echo "{$stateName}: {$count} localities\n";
}