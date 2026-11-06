<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Domains\Spatial\Models\MapLayer;
use Illuminate\Support\Facades\DB;

$layers = MapLayer::all();
echo "=== ALL LAYERS ===\n";
foreach ($layers as $l) {
    $count = DB::table('geo_features')->where('layer_id', $l->id)->count();
    $sampleType = DB::table('geo_features')->where('layer_id', $l->id)->whereNotNull('geom')->value(DB::raw("ST_GeometryType(geom)"));
    echo "{$l->id}: name='{$l->name}' type='{$l->type}' is_public={$l->is_public} features={$count} geom_type=" . ($sampleType ?? 'N/A') . "\n";
}
