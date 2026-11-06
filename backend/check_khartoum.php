<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find "الخرطوم" features and their layers
$features = DB::select("
    SELECT gf.id, gf.properties->>'name' as name, ml.name as layer_name, ml.type as layer_type, ST_GeometryType(gf.geom) as geom_type
    FROM geo_features gf
    JOIN map_layers ml ON ml.id = gf.layer_id
    WHERE gf.properties->>'name' LIKE '%خرطوم%'
    LIMIT 20
");

foreach ($features as $f) {
    echo "id={$f->id} name='{$f->name}' layer='{$f->layer_name}' layer_type='{$f->layer_type}' geom='{$f->geom_type}'\n";
}

// Also check what features at the specific coordinates
echo "\n=== Features near (32.5232, 15.5871) ===\n";
$near = DB::select("
    SELECT gf.id, gf.properties->>'name' as name, ml.name as layer_name, ST_Distance(gf.geom::geography, ST_MakePoint(32.5232, 15.5871)::geography) as dist
    FROM geo_features gf
    JOIN map_layers ml ON ml.id = gf.layer_id
    WHERE ST_DWithin(gf.geom::geography, ST_MakePoint(32.5232, 15.5871)::geography, 100)
    ORDER BY dist ASC LIMIT 10
");

foreach ($near as $n) {
    echo "name='{$n->name}' layer='{$n->layer_name}' dist={$n->dist}\n";
}
