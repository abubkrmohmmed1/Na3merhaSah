<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$names = Illuminate\Support\Facades\DB::table('geo_features')
    ->select(DB::raw("geo_features.properties ->> 'name' as name"))
    ->whereExists(function($q) {
        $q->select(DB::raw(1))
          ->from('map_layers')
          ->whereColumn('geo_features.layer_id', 'map_layers.id')
          ->where('map_layers.name', 'المحليات');
    })
    ->limit(20)
    ->pluck('name')
    ->toArray();

print_r($names);