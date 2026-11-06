<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;

// Get the map layers
$localitiesLayer = MapLayer::where('name', 'المحليات')->first();
$statesLayer = MapLayer::where('name', 'الولايات')->first();

if (!$localitiesLayer || !$statesLayer) {
    echo "Error: Could not find required map layers\n";
    exit;
}

echo "Localities Layer ID: {$localitiesLayer->id}\n";
echo "States Layer ID: {$statesLayer->id}\n\n";

// Get the three states
$states = GeoFeature::whereHas('mapLayer', function($q) {
    $q->where('name', 'الولايات');
})->get();

$stateMap = [];
foreach ($states as $state) {
    $properties = $state->properties;
    if (is_string($properties)) {
        $properties = json_decode($properties, true);
    }
    $stateMap[$properties['name'] ?? 'Unknown'] = $state->id;
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
        $existing = GeoFeature::whereHas('mapLayer', function($q) {
            $q->where('name', 'المحليات');
        })
        ->whereRaw("properties ->> 'name' = ?", [$name])
        ->first();
        
        if ($existing) {
            echo "  - $name already exists (ID: {$existing->id}), skipping\n";
            $skippedCount++;
            continue;
        }
        
        // Create the locality using the model (will trigger boot events)
        $locality = new GeoFeature([
            'layer_id' => $localitiesLayer->id,
            'properties' => [
                'name' => $name,
                'type' => 'locality'
            ],
            'status' => 'active'
        ]);
        
        // Set the geometry using a raw expression for the point
        $locality->geom = DB::raw("ST_GeomFromText('POINT($lng $lat)', 4326)");
        
        // Save the locality (this will trigger the boot method and autoDetectParent)
        $locality->save();
        
        // Manually set the parent_id to ensure it's linked to the correct state
        // (autoDetectParent might not work if the locality point is not within state bounds)
        $locality->parent_id = $stateId;
        $locality->save();
        
        echo "  - Created $name (ID: {$locality->id}) with parent $stateName\n";
        $createdCount++;
    }
}

echo "\n=== Summary ===\n";
echo "Created: $createdCount localities\n";
echo "Skipped (already existed): $skippedCount localities\n";

// Verify counts
echo "\n=== Verification ===\n";
foreach ($stateMap as $stateName => $stateId) {
    $count = GeoFeature::whereHas('mapLayer', function($q) {
        $q->where('name', 'المحليات');
    })
    ->where('parent_id', $stateId)
    ->count();
    echo "$stateName: $count localities\n";
}

$totalLocalities = GeoFeature::whereHas('mapLayer', function($q) {
    $q->where('name', 'المحليات');
})->count();
echo "Total localities in system: $totalLocalities\n";

echo "\n=== Sample of created localities ===\n";
$samples = GeoFeature::whereHas('mapLayer', function($q) {
    $q->where('name', 'المحليات');
})
->whereNotNull('parent_id')
->with(['mapLayer', 'parent']) // Eager load relationships
->limit(10)
->get();

foreach ($samples as $sample) {
    $localityName = $sample->properties['name'] ?? 'Unknown';
    $parentName = $sample->parent ? ($sample->parent->properties['name'] ?? 'Unknown') : 'None';
    echo "{$localityName} -> {$parentName}\n";
}
