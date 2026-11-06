<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

// Get the map layers
$localitiesLayerId = DB::table('map_layers')->where('name', 'المحليات')->value('id');
$statesLayerId = DB::table('map_layers')->where('name', 'الولايات')->value('id');

echo "Localities Layer ID: $localitiesLayerId\n";
echo "States Layer ID: $statesLayerId\n\n";

// Get the three states
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

// Define the EXACT localities from user's coordinate data
$localitiesToCreate = [
    // الخرطوم (7 localities)
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

foreach ($localitiesToCreate as $stateName => $localities) {
    if (!isset($stateMap[$stateName])) {
        echo "تنبيه: الولاية '$stateName' غير موجودة في قاعدة البيانات\n";
        continue;
    }

    $stateId = $stateMap[$stateName];
    echo "\nإنشاء محليات لـ $stateName:\n";

    foreach ($localities as $localityData) {
        $name = $localityData['name'];
        $lat = $localityData['lat'];
        $lng = $localityData['lng'];

        // Generate UUID
        $uuid = Uuid::uuid4()->toString();

        // Insert with explicit UUID
        DB::unprepared("
            INSERT INTO geo_features (id, layer_id, geom, properties, parent_id, status, created_at, updated_at)
            VALUES (
                '$uuid',
                '$localitiesLayerId',
                ST_GeomFromText('POINT($lng $lat)', 4326),
                '{\"name\": \"$name\", \"type\": \"locality\"}',
                '$stateId',
                'active',
                NOW(),
                NOW()
            )
        ");

        echo "  - تم إنشاء $name\n";
        $createdCount++;
    }
}

echo "\n=== الملخص ===\n";
echo "تم إنشاء $createdCount محلية\n";

// Verify
echo "\n=== التحقق ===\n";
$expected = ['الخرطوم' => 7, 'الجزيرة' => 8, 'القضارف' => 12];
foreach ($stateMap as $stateName => $stateId) {
    $count = DB::table('geo_features')
        ->whereExists(function($query) {
            $query->select(DB::raw(1))
                  ->from('map_layers')
                  ->whereColumn('geo_features.layer_id', 'map_layers.id')
                  ->where('map_layers.name', 'المحليات');
        })
        ->where('parent_id', $stateId)
        ->count();
    $exp = $expected[$stateName] ?? 0;
    $status = $count == $exp ? "✅" : "❌";
    echo "  $stateName: $count/$exp محلية $status\n";
}

$total = DB::table('geo_features')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('map_layers')
              ->whereColumn('geo_features.layer_id', 'map_layers.id')
              ->where('map_layers.name', 'المحليات');
    })
    ->count();
echo "\nإجمالي المحليات: $total\n";