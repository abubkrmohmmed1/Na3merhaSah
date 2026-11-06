<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Domains\Spatial\Models\GeoFeature;

echo "=== Testing autoDetectParent() by creating a test locality ===\n\n";

// Get one of the states to confirm it exists
$states = GeoFeature::whereHas('mapLayer', function($q) {
    $q->where('name', 'الولايات');
})->get();

echo "States found:\n";
foreach ($states as $state) {
    $properties = $state->properties;
    if (is_string($properties)) {
        $properties = json_decode($properties, true);
    }
    $name = $properties['name'] ?? 'Unknown';
    echo "  - $name (ID: {$state->id})\n";
}

// Create a test locality at a known coordinate within one of the states
// Let's use a point that should definitely be in الخرطوم state based on user's data
$testLat = 15.50;
$testLng = 32.50;

echo "\nCreating test locality at ($testLat, $testLng) which should be in الخرطوم state\n";

$locality = new GeoFeature([
    'layer_id' => DB::table('map_layers')->where('name', 'المحليات')->value('id'),
    'geom' => DB::raw("ST_GeomFromText('POINT($testLng $testLat)', 4326)"),
    'properties' => [
        'name' => 'Test Locality for AutoDetect',
        'type' => 'test'
    ],
    'status' => 'active'
]);

echo "Saving locality...\n";
$locality->save();

echo "Refreshing from database...\n";
$locality->refresh();

$parentId = $locality->parent_id;
echo "Parent ID after save: ";
if ($parentId) {
    $parent = GeoFeature::find($parentId);
    if ($parent) {
        $parentProperties = $parent->properties;
        if (is_string($parentProperties)) {
            $parentProperties = json_decode($parentProperties, true);
        }
        $parentName = $parentProperties['name'] ?? 'Unknown';
        echo "$parentName (ID: $parentId)\n";
    } else {
        echo "Parent not found in database\n";
    }
} else {
    echo "None (not linked to any parent)\n";
}

// Clean up
echo "Cleaning up test locality...\n";
$locality->delete();

echo "\n=== Test Complete ===\n";
echo "If autoDetectParent() is working correctly, the test locality should have been\n";
echo "automatically linked to the الخرطوم state.\n";