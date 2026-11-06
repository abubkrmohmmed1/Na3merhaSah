<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

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

// Get states
$states = DB::table('geo_features')
    ->select('geo_features.id', 'geo_features.properties', 'geo_features.geom')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'الولايات');
    })
    ->get();

echo "Found " . count($states) . " states.\n";

// Get localities without parent
$localities = DB::table('geo_features')
    ->select('geo_features.id', 'geo_features.properties', 'geo_features.geom')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->whereNull('geo_features.parent_id')
    ->get();

echo "Found " . count($localities) . " localities without parent.\n";

$linked = 0;
foreach ($localities as $locality) {
    $locProps = json_decode($locality->properties, true);
    $locName = $locProps['name'] ?? 'Unknown';
    $locGeom = $locality->geom;

    if (!$locGeom) {
        continue;
    }

    // Try to find a state that contains this locality
    foreach ($states as $state) {
        $stateProps = json_decode($state->properties, true);
        $stateName = $stateProps['name'] ?? 'Unknown';
        $stateGeom = $state->geom;

        if (!$stateGeom) {
            continue;
        }

        // Check if state contains locality
        $contains = DB::selectOne("
            SELECT ST_Contains(?, ?) as contains
        ", [$stateGeom, $locGeom]);

        if ($contains && $contains->contains) {
            DB::table('geo_features')
                ->where('id', $locality->id)
                ->update(['parent_id' => $state->id]);
            
            $linked++;
            echo "Linked: $locName -> $stateName\n";
            break;
        }
    }
}

echo "\nLinked $linked localities to states.\n";

// Show stats per state
foreach ($states as $state) {
    $stateProps = json_decode($state->properties, true);
    $stateName = $stateProps['name'] ?? 'Unknown';
    
    $count = DB::table('geo_features')
        ->where('parent_id', $state->id)
        ->count();
    
    echo "$stateName: $count localities\n";
}