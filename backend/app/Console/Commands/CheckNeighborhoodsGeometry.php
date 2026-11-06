<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;

class CheckNeighborhoodsGeometry extends Command
{
    protected $signature = 'map:check-neighborhoods';

    protected $description = 'Check geometry types of neighborhoods in DB';

    public function handle()
    {
        $results = DB::select("
            SELECT ST_GeometryType(geom) as type, count(*) as cnt
            FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'الأحياء'
            GROUP BY ST_GeometryType(geom)
            ORDER BY cnt DESC
        ");

        $this->info("Neighborhoods Geometry Types:");
        foreach ($results as $row) {
            $this->line("  {$row->type}: {$row->cnt}");
        }

        $total = 0;
        foreach ($results as $row) { $total += $row->cnt; }
        $this->info("Total neighborhoods: $total");

        // Also check what layers exist
        $layers = MapLayer::all();
        $this->newLine();
        $this->info("Map Layers:");
        foreach ($layers as $layer) {
            $count = GeoFeature::where('layer_id', $layer->id)->count();
            $this->line("  - {$layer->name}: $count features");
        }
    }
}