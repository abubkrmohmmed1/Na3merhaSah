<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Domains\Spatial\Models\GeoFeature;

// Check what geometries exist for states and localities
echo "=== State Geometry Types ===\n";
$states = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))->get();
foreach ($states as $state) {
    $props = is_array($state->properties) ? $state->properties : json_decode($state->properties, true);
    $name = $props['name'] ?? 'Unknown';
    $geomType = DB::selectOne("SELECT ST_GeometryType(?) as type FROM geo_features WHERE id = ?", [$state->geom, $state->id]);
    echo "$name: {$geomType->type}\n";

    // Get sub-maps
    $localities = GeoFeature::where('parent_id', $state->id)
        ->whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
        ->get();

    foreach ($localities as $locality) {
        $locProps = is_array($locality->properties) ? $locality->properties : json_decode($locality->properties, true);
        $locName = $locProps['name'] ?? 'Unknown';
        $locGeomType = DB::selectOne("SELECT ST_GeometryType(?) as type FROM geo_features WHERE id = ?", [$locality->geom, $locality->id]);
        echo "  - $locName: {$locGeomType->type}\n";
    }
}

echo "\n=== Admin Boundaries GeoJSON ===\n";
$features = GeoFeature::select(['id', 'layer_id', 'properties', 'geom'])
    ->whereHas('mapLayer', fn($q) => $q->whereIn('name', ['الولايات', 'المحليات', 'الأحياء']))
    ->whereNotNull('geom')
    ->limit(5)
    ->get();

foreach ($features as $f) {
    $props = is_array($f->properties) ? $f->properties : json_decode($f->properties, true);
    $name = $props['name'] ?? 'Unknown';
    echo "$name: geom type = " . (isset($f->geom) ? 'SET' : 'NULL') . "\n";
}