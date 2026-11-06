<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Check if the localities we created are actually within the state boundaries
echo "Checking if localities are within state boundaries using ST_Contains...\n\n";

// Get the three states
$states = DB::table('geo_features')
    ->select('geo_features.id', 
             DB::raw("geo_features.properties ->> 'name' as state_name"),
             'geo_features.geom as state_geom')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'الولايات');
    })
    ->get();

echo "Found " . count($states) . " states:\n";
foreach ($states as $state) {
    $stateName = $state->state_name;
    echo "  - $stateName\n";
}

// Get localities that have been manually linked (the ones we created)
$localities = DB::table('geo_features')
    ->select('geo_features.id', 
             DB::raw("geo_features.properties ->> 'name' as locality_name"),
             'geo_features.geom as locality_geom',
             'geo_features.parent_id')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->whereNotNull('geo_features.parent_id')
    ->get();

echo "\nFound " . count($localities) . " localities with parent_id set:\n";

$mismatchCount = 0;
foreach ($localities as $locality) {
    $localityName = $locality->locality_name;
    $localityGeom = $locality->locality_geom;
    $parentId = $locality->parent_id;
    
    // Get the parent state
    $parentState = $states->firstWhere('id', $parentId);
    if (!$parentState) {
        echo "  - $localityName: ERROR - Parent state not found\n";
        continue;
    }
    
    $parentStateName = $parentState->state_name;
    $parentStateGeom = $parentState->state_geom;
    
    // Check if state actually contains locality
    $contains = DB::selectOne("
        SELECT ST_Contains(?, ?) as contains
    ", [$parentStateGeom, $localityGeom]);
    
    $isContained = $contains && $contains->contains;
    
    if (!$isContained) {
        $mismatchCount++;
        echo "  - $localityName (parent: $parentStateName): NOT CONTAINED ❌\n";
    } else {
        echo "  - $localityName (parent: $parentStateName): contained ✅\n";
    }
}

echo "\n=== Summary ===\n";
echo "Localities with parent_id set: " . count($localities) . "\n";
echo "Actually contained within parent state: " . (count($localities) - $mismatchCount) . "\n";
echo "Mismatches (not actually contained): " . $mismatchCount . "\n";

if ($mismatchCount > 0) {
    echo "\n⚠️  Warning: Some localities are linked to states they don't actually fall within!\n";
    echo "This explains why autoDetectParent() doesn't work - the existing linkages\n";
    echo "were done manually, not based on geometric containment.\n";
} else {
    echo "\n✅ All localities are correctly contained within their parent states.\n";
    echo "The autoDetectParent() method should work for new localities.\n";
}
