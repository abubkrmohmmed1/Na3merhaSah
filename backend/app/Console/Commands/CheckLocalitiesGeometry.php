<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;

class CheckLocalitiesGeometry extends Command
{
    protected $signature = 'map:check-localities';

    protected $description = 'Check geometry types of localities in DB';

    public function handle()
    {
        $results = DB::select("
            SELECT ST_GeometryType(geom) as type, count(*) as cnt
            FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'المحليات'
            GROUP BY ST_GeometryType(geom)
            ORDER BY cnt DESC
        ");

        $this->info("Localities Geometry Types:");
        foreach ($results as $row) {
            $this->line("  {$row->type}: {$row->cnt}");
        }

        $total = 0;
        foreach ($results as $row) { $total += $row->cnt; }
        $this->info("Total localities: $total");

        // Show sample
        $this->newLine();
        $this->info("Sample localities:");
        $samples = DB::select("
            SELECT gf.id, gf.properties->>'$.name' as name, ST_GeometryType(geom) as type
            FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'المحليات'
            LIMIT 5
        ");
        foreach ($samples as $s) {
            $this->line("  - {$s->name} ({$s->type})");
        }

        // Check Administrative layer
        $this->newLine();
        $adminCount = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'Administrative'))->count();
        $this->info("Administrative layer: $adminCount features");

        if ($adminCount > 0) {
            $adminResults = DB::select("
                SELECT ST_GeometryType(geom) as type, count(*) as cnt
                FROM geo_features gf
                JOIN map_layers ml ON gf.layer_id = ml.id
                WHERE ml.name = 'Administrative'
                GROUP BY ST_GeometryType(geom)
                ORDER BY cnt DESC
            ");
            $this->info("  Geometry types:");
            foreach ($adminResults as $row) {
                $this->line("    {$row->type}: {$row->cnt}");
            }
        }
    }
}