<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugLocalityNames extends Command
{
    protected $signature = 'map:debug-locality-names';

    protected $description = 'Debug locality names from DB';

    public function handle()
    {
        $results = DB::select("
            SELECT gf.id, properties->>'name' as name, ST_GeometryType(geom) as type, ST_AsText(ST_Centroid(geom)) as center
            FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'المحليات'
            LIMIT 5
        ");

        foreach ($results as $r) {
            $this->line("Name: " . ($r->name ?? 'null'));
            $this->line("Type: " . $r->type);
            $this->line("Center: " . $r->center);
            $this->newLine();
        }

        $total = DB::selectOne("SELECT count(*) as cnt FROM geo_features gf JOIN map_layers ml ON gf.layer_id = ml.id WHERE ml.name = 'المحليات'");
        $this->info("Total: " . $total->cnt);

        $states = DB::select("SELECT gf.id, properties->>'name' as name FROM geo_features gf JOIN map_layers ml ON gf.layer_id = ml.id WHERE ml.name = 'الولايات' ORDER BY properties->>'name'");
        $this->newLine();
        $this->info("States: " . count($states));
        foreach ($states as $s) {
            $this->line("  - " . ($s->name ?? 'null'));
        }
    }
}