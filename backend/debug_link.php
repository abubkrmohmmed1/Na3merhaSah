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
foreach ($localities as $index => $locality) {
    if ($index >= 5) {
        // Only check first 5 localities for debug
        break;
    }
    
    $locProps = json_decode($locality->properties, true);
    $locName = $locProps['name'] ?? 'Unknown';
    $locGeom = $locality->geom;

    if (!$locGeom) {
        echo "Locality $locName has no geometry\n";
        continue;
    }

    // Get locality coordinates
    $locCoord = DB::selectOne("
        SELECT ST_X(?) as lng, ST_Y(?) as lat
    ", [$locGeom, $locGeom]);

    echo "Locality: $locName at ({$locCoord->lat}, {$locCoord->lng})\n";

    foreach ($states as $state) {
        $stateProps = json_decode($state->properties, true);
        $stateName = $stateProps['name'] ?? 'Unknown';
        $stateGeom = $state->geom;

        if (!$stateGeom) {
            continue;
        }

        // Get state bounds (envelope)
        $stateEnv = DB::selectOne("
            SELECT 
                ST_XMin(?) as minx, 
                ST_YMin(?) as miny,
                ST_XMax(?) as maxx,
                ST_YMax(?) as maxy
        ", [$stateGeom, $stateGeom, $stateGeom, $stateGeom]);

        echo "  State $stateName: [{$stateEnv->miny},{$stateEnv->minx}] to [{$stateEnv->maxy},{$stateEnv->maxx}]\n";

        // Check if point is within envelope first
        $inEnv = ($locCoord->lat >= $stateEnv->miny && $locCoord->lat <= $stateEnv->maxy &&
                  $locCoord->lng >= $stateEnv->minx && $locCoord->lng <= $stateEnv->maxx);

        echo "  Point in envelope: " . ($inEnv ? 'yes' : 'no') . "\n";

        if ($inEnv) {
            // Check containment
            $contains = DB::selectOne("
                SELECT ST_Contains(?, ?) as contains
            ", [$stateGeom, $locGeom]);

            echo "  ST_Contains result: " . ($contains && $contains->contains ? 'true' : 'false') . "\n";

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
    echo "\n";
}

echo "\nLinked $linked localities to states (from first 5 checked).\n";

// Now run the full linking without debug to see if any match
$linkedFull = 0;
foreach ($localities as $locality) {
    $locProps = json_decode($locality->properties, true);
    $locName = $locProps['name'] ?? 'Unknown';
    $locGeom = $locality->geom;

    if (!$locGeom) {
        continue;
    }

    foreach ($states as $state) {
        $stateProps = json_decode($state->properties, true);
        $stateName = $stateProps['name'] ?? 'Unknown';
        $stateGeom = $state->geom;

        if (!$stateGeom) {
            continue;
        }

        $contains = DB::selectOne("
            SELECT ST_Contains(?, ?) as contains
        ", [$stateGeom, $locGeom]);

        if ($contains && $contains->contains) {
            DB::table('geo_features')
                ->where('id', $locality->id)
                ->update(['parent_id' => $state->id]);
            
            $linkedFull++;
            break;
        }
    }
}

echo "\nFull run: Linked $linkedFull localities to states.\n";

// Show stats per state
foreach ($states as $state) {
    $stateProps = json_decode($state->properties, true);
    $stateName = $stateProps['name'] ?? 'Unknown';
    
    $count = DB::table('geo_features')
        ->where('parent_id', $state->id)
        ->count();
    
    echo "$stateName: $count localities\n";
}