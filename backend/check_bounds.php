<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== فحص الحدود الجغرافية للبيانات ===\n\n";

$states = \App\Domains\Spatial\Models\GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))->get();

foreach($states as $state) {
    $name = $state->properties['name'] ?? 'Unknown';
    $result = \Illuminate\Support\Facades\DB::selectOne("
        SELECT
            ST_X(ST_Envelope(?)) as min_x,
            ST_Y(ST_Envelope(?)) as min_y,
            ST_X(ST_ShiftLongitude(ST_Envelope(?))) as max_x,
            ST_Y(ST_Envelope(?)) as max_y
        FROM geo_features WHERE id = ?
    ", [$state->geom, $state->geom, $state->geom, $state->geom, $state->id]);

    echo "$name:\n";
    echo "  lng: {$result->min_x} to {$result->max_x}\n";
    echo "  lat: {$result->min_y} to {$result->max_y}\n\n";
}

echo "\n=== sample localities coordinates ===\n";
$localities = \App\Domains\Spatial\Models\GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))->limit(20)->get();

foreach($localities as $loc) {
    $name = $loc->properties['name'] ?? 'Unknown';
    $result = \Illuminate\Support\Facades\DB::selectOne("
        SELECT ST_X(?) as lng, ST_Y(?) as lat FROM geo_features WHERE id = ?
    ", [$loc->geom, $loc->geom, $loc->id]);
    echo "$name: {$result->lat}, {$result->lng}\n";
}