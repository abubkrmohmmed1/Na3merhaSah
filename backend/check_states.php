<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Get the three states
$states = DB::table('geo_features')
    ->select('geo_features.id', 'geo_features.properties')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'الولايات');
    })
    ->get();

echo "States in database:\n";
foreach ($states as $state) {
    $props = json_decode($state->properties, true);
    $name = $props['name'] ?? 'Unknown';
    echo "- ID: {$state->id}, Name: {$name}\n";
}

// Get current localities count
$localityCount = DB::table('geo_features')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->count();

echo "\nCurrent localities count: {$localityCount}\n";

// Check how many localities have parent_id set
$localitiesWithParent = DB::table('geo_features')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->whereNotNull('parent_id')
    ->count();

echo "Localities with parent_id set: {$localitiesWithParent}\n";
