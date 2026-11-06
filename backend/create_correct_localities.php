<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Get the three states and their IDs
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

$stateMap = [];
foreach ($states as $state) {
    $stateMap[$state->state_name] = $state->id;
}

echo "States found:\n";
foreach ($stateMap as $name => $id) {
    echo "  $name: $id\n";
}

// Define the localities to create based on user's coordinate data
$localitiesToCreate = [
    // الخرطوم (13 localities) - coordinates from user's message
    'الخرطوم' => [
        ['name' => 'الخرطوم', 'lat' => 15.42, 'lng' => 32.42],
        ['name' => 'أم درمان', 'lat' => 15.52, 'lng' => 32.32],
        ['name' => 'الخرطوم بحري', 'lat' => 15.58, 'lng' => 32.50],
        ['name' => 'شرق النيل', 'lat' => 15.25, 'lng' => 32.65],
        ['name' => 'جبل أولياء', 'lat' => 15.05, 'lng' => 32.35],
        ['name' => 'أمبدة', 'lat' => 15.40, 'lng' => 31.80],
        ['name' => 'كرري', 'lat' => 15.70, 'lng' => 32.25],
    ],
    // الجزيرة (8 localities)
    'الجزيرة' => [
        ['name' => 'مدني الكبرى', 'lat' => 14.30, 'lng' => 33.38],
        ['name' => 'جنوب الجزيرة', 'lat' => 14.00, 'lng' => 33.30],
        ['name' => 'شرق الجزيرة', 'lat' => 14.35, 'lng' => 33.35],
        ['name' => 'الحصاحيصا', 'lat' => 14.55, 'lng' => 33.05],
        ['name' => 'الكاملين', 'lat' => 14.90, 'lng' => 32.80],
        ['name' => 'المناقل', 'lat' => 13.85, 'lng' => 32.65],
        ['name' => 'أم القرى', 'lat' => 14.05, 'lng' => 33.70],
        ['name' => 'القرشي', 'lat' => 13.90, 'lng' => 32.85],
    ],
    // القضارف (12 localities)
    'القضارف' => [
        ['name' => 'القضارف (البلدية)', 'lat' => 13.98, 'lng' => 35.32],
        ['name' => 'وسط القضارف', 'lat' => 13.75, 'lng' => 35.15],
        ['name' => 'الفاو', 'lat' => 13.95, 'lng' => 34.10],
        ['name' => 'الفشقة', 'lat' => 13.80, 'lng' => 35.70],
        ['name' => 'القلابات الشرقية', 'lat' => 12.70, 'lng' => 35.80],
        ['name' => 'القلابات الغربية', 'lat' => 12.45, 'lng' => 35.10],
        ['name' => 'قلع النحل', 'lat' => 13.30, 'lng' => 34.60],
        ['name' => 'الرهد', 'lat' => 12.55, 'lng' => 34.35],
        ['name' => 'البطانة', 'lat' => 14.30, 'lng' => 34.50],
        ['name' => 'باسندة', 'lat' => 12.50, 'lng' => 35.85],
        ['name' => 'القريشة', 'lat' => 13.55, 'lng' => 35.80],
        ['name' => 'المفازة', 'lat' => 13.15, 'lng' => 34.30],
    ]
];

// Get the map layers
$localitiesLayerId = DB::table('map_layers')->where('name', 'المحليات')->value('id');
$statesLayerId = DB::table('map_layers')->where('name', 'الولايات')->value('id');

echo "\nLayer IDs:\n";
echo "  محليات: $localitiesLayerId\n";
echo "  ولايات: $statesLayerId\n";

$createdCount = 0;
$skippedCount = 0;

foreach ($localitiesToCreate as $stateName => $localities) {
    if (!isset($stateMap[$stateName])) {
        echo "Warning: State '$stateName' not found in database\n";
        continue;
    }
    
    $stateId = $stateMap[$stateName];
    echo "\nCreating localities for $stateName:\n";
    
    foreach ($localities as $localityData) {
        $name = $localityData['name'];
        $lat = $localityData['lat'];
        $lng = $localityData['lng'];
        
        // Check if locality already exists (by name)
        $existing = DB::table('geo_features')
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('map_layers')
                      ->whereColumn('geo_features.layer_id', 'map_layers.id')
                      ->where('map_layers.name', 'المحليات');
            })
            ->whereRaw("geo_features.properties ->> 'name' = ?", [$name])
            ->first();
        
        if ($existing) {
            echo "  - $name already exists (ID: {$existing->id}), skipping\n";
            $skippedCount++;
            continue;
        }
        
        // Create point geometry from coordinates - note: PostGIS uses POINT(lng lat)
        $wkt = sprintf("POINT(%F %F)", $lng, $lat);
        
        // Insert the locality
        $id = DB::table('geo_features')->insertGetId([
            'layer_id' => $localitiesLayerId,
            'geom' => DB::raw("ST_GeomFromText('$wkt', 4326)"),
            'properties' => json_encode([
                'name' => $name,
                'type' => 'locality'
            ], JSON_UNESCAPED_UNICODE),
            'parent_id' => $stateId,  // Set parent immediately
            'status' => 'active'
        ]);
        
        echo "  - Created $name (ID: $id) with parent $stateName\n";
        $createdCount++;
    }
}

echo "\n=== Summary ===\n";
echo "Created: $createdCount localities\n";
echo "Skipped (already existed): $skippedCount localities\n";

// Verify counts
echo "\n=== Verification ===\n";
foreach ($stateMap as $stateName => $stateId) {
    $count = DB::table('geo_features')
        ->where('layer_id', $localitiesLayerId)
        ->where('parent_id', $stateId)
        ->count();
    echo "$stateName: $count localities\n";
}

$totalLocalities = DB::table('geo_features')
    ->where('layer_id', $localitiesLayerId)
    ->count();
echo "Total localities in system: $totalLocalities\n";

echo "\n=== Sample of created localities ===\n";
$samples = DB::table('geo_features')
    ->select('geo_features.id', 
             DB::raw("geo_features.properties ->> 'name' as name"),
             DB::raw("ST_X(geo_features.geom) as longitude"),
             DB::raw("ST_Y(geo_features.geom) as latitude"),
             DB::raw("(SELECT properties ->> 'name' FROM geo_features WHERE id = geo_features.parent_id) as parent_name"))
    ->where('geo_features.layer_id', $localitiesLayerId)
    ->whereNotNull('geo_features.parent_id')
    ->limit(10)
    ->get();

foreach ($samples as $sample) {
    echo "{$sample->name} (lat: {$sample->latitude}, lng: {$sample->longitude}) -> {$sample->parent_name}\n";
}