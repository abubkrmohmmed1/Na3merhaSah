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

// Define the exact bounds from user's data for matching
$stateLocalities = [
    'الخرطوم' => [
        'الخرطوم' => ['lat' => 15.42, 'lng' => 32.42, 'lat_range' => [15.42, 15.62], 'lng_range' => [32.42, 32.60]],
        'أم درمان' => ['lat' => 15.52, 'lng' => 32.32, 'lat_range' => [15.52, 15.75], 'lng_range' => [32.32, 32.55]],
        'الخرطوم بحري' => ['lat' => 15.58, 'lng' => 32.50, 'lat_range' => [15.58, 15.95], 'lng_range' => [32.50, 32.80]],
        'شرق النيل' => ['lat' => 15.25, 'lng' => 32.65, 'lat_range' => [15.25, 16.20], 'lng_range' => [32.65, 33.50]],
        'جبل أولياء' => ['lat' => 15.05, 'lng' => 32.35, 'lat_range' => [15.05, 15.45], 'lng_range' => [32.35, 32.70]],
        'أمبدة' => ['lat' => 15.40, 'lng' => 31.80, 'lat_range' => [15.40, 16.10], 'lng_range' => [31.80, 32.45]],
        'كرري' => ['lat' => 15.70, 'lng' => 32.25, 'lat_range' => [15.70, 16.25], 'lng_range' => [32.25, 32.60]],
    ],
    'الجزيرة' => [
        'مدني الكبرى' => ['lat' => 14.30, 'lng' => 33.38, 'lat_range' => [14.30, 14.58], 'lng_range' => [33.38, 33.65]],
        'جنوب الجزيرة' => ['lat' => 14.00, 'lng' => 33.30, 'lat_range' => [14.00, 14.40], 'lng_range' => [33.30, 33.85]],
        'شرق الجزيرة' => ['lat' => 14.35, 'lng' => 33.35, 'lat_range' => [14.35, 15.20], 'lng_range' => [33.35, 34.15]],
        'الحصاحيصا' => ['lat' => 14.55, 'lng' => 33.05, 'lat_range' => [14.55, 14.95], 'lng_range' => [33.05, 33.55]],
        'الكاملين' => ['lat' => 14.90, 'lng' => 32.80, 'lat_range' => [14.90, 15.35], 'lng_range' => [32.80, 33.30]],
        'المناقل' => ['lat' => 13.85, 'lng' => 32.65, 'lat_range' => [13.85, 14.50], 'lng_range' => [32.65, 33.25]],
        'أم القرى' => ['lat' => 14.05, 'lng' => 33.70, 'lat_range' => [14.05, 14.65], 'lng_range' => [33.70, 34.35]],
        'القرشي' => ['lat' => 13.90, 'lng' => 32.85, 'lat_range' => [13.90, 14.35], 'lng_range' => [32.85, 33.40]],
    ],
    'القضارف' => [
        'القضارف (البلدية)' => ['lat' => 13.98, 'lng' => 35.32, 'lat_range' => [13.98, 14.10], 'lng_range' => [35.32, 35.45]],
        'وسط القضارف' => ['lat' => 13.75, 'lng' => 35.15, 'lat_range' => [13.75, 14.30], 'lng_range' => [35.15, 35.65]],
        'الفاو' => ['lat' => 13.95, 'lng' => 34.10, 'lat_range' => [13.95, 14.45], 'lng_range' => [34.10, 34.70]],
        'الفشقة' => ['lat' => 13.80, 'lng' => 35.70, 'lat_range' => [13.80, 14.80], 'lng_range' => [35.70, 36.30]],
        'القلابات الشرقية' => ['lat' => 12.70, 'lng' => 35.80, 'lat_range' => [12.70, 13.50], 'lng_range' => [35.80, 36.40]],
        'القلابات الغربية' => ['lat' => 12.45, 'lng' => 35.10, 'lat_range' => [12.45, 13.60], 'lng_range' => [35.10, 35.85]],
        'قلع النحل' => ['lat' => 13.30, 'lng' => 34.60, 'lat_range' => [13.30, 13.95], 'lng_range' => [34.60, 35.25]],
        'الرهد' => ['lat' => 12.55, 'lng' => 34.35, 'lat_range' => [12.55, 13.65], 'lng_range' => [34.35, 35.30]],
        'البطانة' => ['lat' => 14.30, 'lng' => 34.50, 'lat_range' => [14.30, 15.80], 'lng_range' => [34.50, 35.80]],
        'باسندة' => ['lat' => 12.50, 'lng' => 35.85, 'lat_range' => [12.50, 13.15], 'lng_range' => [35.85, 36.45]],
        'القريشة' => ['lat' => 13.55, 'lng' => 35.80, 'lat_range' => [13.55, 14.15], 'lng_range' => [35.80, 36.35]],
        'المفازة' => ['lat' => 13.15, 'lng' => 34.30, 'lat_range' => [13.15, 13.70], 'lng_range' => [34.30, 34.85]],
    ]
];

// Get all localities
$localities = DB::table('geo_features')
    ->select('geo_features.id', 'geo_features.properties', 'geo_features.geom')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->get();

echo "Found " . count($localities) . " localities.\n";

$linked = ['الخرطوم' => 0, 'الجزيرة' => 0, 'القضارف' => 0];
$notFound = [];

foreach ($localities as $locality) {
    $locProps = json_decode($locality->properties, true);
    $locName = $locProps['name'] ?? 'Unknown';
    $locGeom = $locality->geom;

    if (!$locGeom) {
        $notFound[] = $locName . " (no geometry)";
        continue;
    }

    // Get locality coordinates
    $coord = DB::selectOne("
        SELECT ST_X(?) as lng, ST_Y(?) as lat
    ", [$locGeom, $locGeom]);

    $lat = $coord->lat;
    $lng = $coord->lng;

    $matched = false;

    // Check each state's localities
    foreach ($stateLocalities as $stateName => $localitiesList) {
        foreach ($localitiesList as $localityName => $info) {
            // Check if names match or are similar
            $nameMatch = (
                str_contains(strtolower($locName), strtolower($localityName)) ||
                str_contains(strtolower($localityName), strtolower($locName)) ||
                // Check for common variations
                (
                    str_contains(strtolower($locName), 'المحلية') &&
                    str_contains(strtolower($localityName), 'المحلية')
                ) ||
                // Remove common suffixes/prefixes and compare
                (
                    preg_match('/[^\s]+/', str_replace(['المحلية', '(البلدية)', 'شرق', 'غرب', 'وسط', 'شمال', 'جنوب'], '', strtolower($locName)), $matches1) &&
                    preg_match('/[^\s]+/', str_replace(['المحلية', '(البلدية)', 'شرق', 'غرب', 'وسط', 'شمال', 'جنوب'], '', strtolower($localityName)), $matches2) &&
                    isset($matches1[0]) && isset($matches2[0]) && $matches1[0] === $matches2[0]
                )
            );

            if ($nameMatch) {
                // Get the state ID from database
                $stateId = DB::table('geo_features')
                    ->whereExists(function($query) use ($stateName) {
                        $query->select(DB::raw(1))
                              ->from('map_layers')
                              ->whereColumn('geo_features.layer_id', 'map_layers.id')
                              ->where('map_layers.name', $stateName);
                    })
                    ->value('geo_features.id');

                if ($stateId) {
                    DB::table('geo_features')
                        ->where('id', $locality->id)
                        ->update(['parent_id' => $stateId]);
                    
                    $linked[$stateName]++;
                    echo "Linked: $locName -> $stateName\n";
                    $matched = true;
                    break 2; // Break out of both loops
                }
            }
        }
    }

    if (!$matched) {
        $notFound[] = $locName . " ({$lat}, {$lng})";
    }
}

echo "\n=== Results ===\n";
foreach ($linked as $state => $count) {
    echo "$state: $count localities linked\n";
}

$totalLinked = array_sum($linked);
echo "\nTotal linked: $totalLinked localities\n";
echo "Not matched: " . count($notFound) . " localities\n";

if (!empty($notFound) && count($notFound) <= 15) {
    echo "Not matched localities (first 15):\n";
    foreach (array_slice($notFound, 0, 15) as $notFoundItem) {
        echo "  - $notFoundItem\n";
    }
    if (count($notFound) > 15) {
        echo "  - ... and " . (count($notFound) - 15) . " more\n";
    }
}

// Final verification
echo "\n=== Final Verification ===\n";
foreach ($stateLocalities as $stateName => $localitiesList) {
    $count = DB::table('geo_features')
        ->whereExists(function($query) use ($stateName) {
            $query->select(DB::raw(1))
                  ->from('map_layers')
                  ->whereColumn('geo_features.layer_id', 'map_layers.id')
                  ->where('map_layers.name', $stateName);
        })
        ->whereNotNull('parent_id')
        ->count();
    
    echo "$stateName: $count localities in hierarchy\n";
}

// Also show total per state from DB
echo "\n=== From Database (using actual state boundaries) ===\n";
$states = DB::table('geo_features')
    ->select('geo_features.id', 'geo_features.properties')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'الولايات');
    })
    ->get();

foreach ($states as $state) {
    $stateProps = json_decode($state->properties, true);
    $stateName = $stateProps['name'] ?? 'Unknown';
    
    $count = DB::table('geo_features')
        ->where('parent_id', $state->id)
        ->count();
    
    echo "$stateName: $count localities\n";
}