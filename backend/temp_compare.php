<?php
$statesLayer = \App\Domains\Spatial\Models\MapLayer::where('name', 'الولايات')->first();
$locLayer = \App\Domains\Spatial\Models\MapLayer::where('name', 'المحليات')->first();
$hoodLayer = \App\Domains\Spatial\Models\MapLayer::where('name', 'الأحياء')->first();
$villageLayer = \App\Domains\Spatial\Models\MapLayer::where('name', 'القرى والمدن')->first();

$state = \App\Domains\Spatial\Models\GeoFeature::where('layer_id', $statesLayer->id)
    ->where('properties->name', 'الجزيرة')->first();

echo "=== مقارنة بيانات الجزيرة ===\n";
echo "State: " . ($state->properties['name'] ?? 'null') . " | id: {$state->id}\n\n";

$localities = \App\Domains\Spatial\Models\GeoFeature::where('layer_id', $locLayer->id)
    ->where('parent_id', $state->id)
    ->get();

echo "المحليات (" . $localities->count() . "):\n";
$refData = [
    'المناقل' => 545,
    'الحصاحيصا' => 400,
    'جنوب الجزيرة' => 375,
    'شرق الجزيرة' => 350,
    'الكاملين' => 300,
    'أم القرى' => 116,
    'القرشي' => 250,
    'ود مدني الكبرى' => 100,
    'مدني الكبرى' => 50,
    'أم القرى' => 116,
];

foreach ($localities as $loc) {
    $name = $loc->properties['name'] ?? 'null';
    $hoodCount = \App\Domains\Spatial\Models\GeoFeature::where('layer_id', $hoodLayer->id)
        ->where('parent_id', $loc->id)->count();
    $villageCount = \App\Domains\Spatial\Models\GeoFeature::where('layer_id', $villageLayer->id)
        ->where('parent_id', $loc->id)->count();
    $ref = $refData[$name] ?? '?';
    $total = $hoodCount + $villageCount;
    echo "  - {$name}: أحياء={$hoodCount}, قرى={$villageCount}, المجموع={$total} (مرجعي≈{$ref})\n";
}

echo "\n=== ملخص عام ===\n";
echo "إجمالي الأحياء: " . \App\Domains\Spatial\Models\GeoFeature::where('layer_id', $hoodLayer->id)->whereNotNull('parent_id')->count() . "\n";
echo "إجمالي القرى: " . \App\Domains\Spatial\Models\GeoFeature::where('layer_id', $villageLayer->id)->count() . "\n";