<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Count localities in the localities layer
$localityCount = DB::table('geo_features')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->count();

echo "Total localities in the localities layer: $localityCount\n";

// Count localities with parent_id set
$localitiesWithParent = DB::table('geo_features')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->whereNotNull('parent_id')
    ->count();

echo "Localities with parent_id set: $localitiesWithParent\n";

// Show a few localities
$sample = DB::table('geo_features')
    ->select('geo_features.id', 
             DB::raw("geo_features.properties ->> 'name' as name"),
             'geo_features.parent_id')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->limit(5)
    ->get();

echo "\nSample localities:\n";
foreach ($sample as $loc) {
    $name = $loc->name;
    echo "  - $name (ID: {$loc->id}) parent_id: " . ($loc->parent_id ?? 'NULL') . "\n";
}
