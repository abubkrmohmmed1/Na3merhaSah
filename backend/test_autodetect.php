<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Domains\Spatial\Models\GeoFeature;

echo "=== Testing autoDetectParent() functionality ===\n\n";

// Get one of the states to test with
$state = GeoFeature::whereHas('mapLayer', function($q) {
    $q->where('name', 'الولايات');
})->first();

if (!$state) {
    echo "Error: No state found\n";
    exit;
}

$stateProperties = $state->properties;
if (is_string($stateProperties)) {
    $stateProperties = json_decode($stateProperties, true);
}
$stateName = $stateProperties['name'] ?? 'Unknown';
echo "Testing with state: $stateName (ID: {$state->id})\n\n";

// Create a test locality within the state bounds
// Using coordinates that should be within the state based on user's data
$testLocalities = [
    ['name' => 'Test Locality 1', 'lat' => 15.50, 'lng' => 32.50], // Should be in الخرطوم
    ['name' => 'Test Locality 2', 'lat' => 14.20, 'lng' => 33.20], // Should be in الجزيرة
    ['name' => 'Test Locality 3', 'lat' => 13.90, 'lng' => 35.20], // Should be in القضارف
];

foreach ($testLocalities as $localityData) {
    echo "Testing locality: {$localityData['name']} at ({$localityData['lat']}, {$localityData['lng']})\n";
    
    // Create a new GeoFeature instance
    $locality = new GeoFeature([
        'layer_id' => DB::table('map_layers')->where('name', 'المحليات')->value('id'),
        'geom' => DB::raw("ST_GeomFromText('POINT({$localityData['lng']} {$localityData['lat']})', 4326)"),
        'properties' => [
            'name' => $localityData['name'],
            'type' => 'test_locality'
        ],
        'status' => 'active'
    ]);
    
    // Save it (this should trigger autoDetectParent() if set up in boot method)
    $locality->save();
    
    // Refresh from database to get updated parent_id
    $locality->refresh();
    
    $parentId = $locality->parent_id;
    $parentName = 'None';
    
    if ($parentId) {
        $parent = GeoFeature::find($parentId);
        if ($parent) {
            $parentProperties = $parent->properties;
            if (is_string($parentProperties)) {
                $parentProperties = json_decode($parentProperties, true);
            }
            $parentName = $parentProperties['name'] ?? 'Unknown';
        }
    }
    
    echo "  -> Auto-linked to parent: $parentName (ID: $parentId)\n\n";
    
    // Clean up test localities
    $locality->delete();
}

echo "=== Testing complete ===\n";
echo "If the autoDetectParent() method is working correctly in the GeoFeature model,\n";
echo "you should see each locality linked to the appropriate state.\n";
echo "If you see 'None' for all, then the autoDetectParent() method may not be\n";
echo "triggering on save, or the ST_Contains check is failing due to geometry issues.\n";