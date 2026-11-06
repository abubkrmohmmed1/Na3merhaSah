<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Get states
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

echo "States and their linked locality counts:\n";
foreach ($states as $state) {
    $stateName = $state->state_name;
    $count = DB::table('geo_features')
        ->whereExists(function($query) {
            $query->select(DB::raw(1))
                  ->from('map_layers')
                  ->whereColumn('geo_features.layer_id', 'map_layers.id')
                  ->where('map_layers.name', 'المحليات');
        })
        ->where('parent_id', $state->id)
        ->count();
    echo "  $stateName: $count localities\n";
}

// Check the total localities
$totalLocalities = DB::table('geo_features')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->count();
echo "\nTotal localities in system: $totalLocalities\n";
