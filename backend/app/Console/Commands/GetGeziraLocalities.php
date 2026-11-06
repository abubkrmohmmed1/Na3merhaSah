<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetGeziraLocalities extends Command
{
    protected $signature = 'map:gezira-localities';

    protected $description = 'Get Gezira localities and their villages';

    public function handle()
    {
        // Get Gezira state ID
        $gedeira = DB::selectOne("
            SELECT gf.id, gf.properties->>'name' as name FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'الولايات' AND (gf.properties->>'name' LIKE '%الجزيرة%' OR gf.properties->>'pcode' = 'SD15')
        ");

        if (!$gedeira) {
            $this->error("Gezira state not found");
            return;
        }

        $this->info("Gezira state: {$gedeira->name} (ID: {$gedeira->id})");

        // Get localities
        $localities = DB::select("
            SELECT gf.id, gf.properties->>'name' as name, gf.properties->>'name_en' as en_name, gf.properties->>'pcode' as pcode,
                   ST_AsText(ST_Centroid(gf.geom)) as center, ST_GeometryType(gf.geom) as type
            FROM geo_features gf
            WHERE parent_id = ?
            ORDER BY gf.properties->>'name'
        ", [$gedeira->id]);

        $this->newLine();
        $this->info("Localities (" . count($localities) . "):");
        foreach ($localities as $loc) {
            $villageCount = DB::selectOne("
                SELECT count(*) as cnt FROM geo_features gf
                JOIN map_layers ml ON gf.layer_id = ml.id
                WHERE gf.parent_id = ? AND ml.name = 'القرى والمدن'
            ", [$loc->id]);
            $this->line("  {$loc->name} | {$loc->en_name} | {$loc->pcode} | {$loc->type} | villages: {$villageCount->cnt}");
        }

        // Check "القرى والمدن" layer
        $villagesLayer = DB::selectOne("SELECT id, name FROM map_layers WHERE name = 'القرى والمدن'");
        if ($villagesLayer) {
            $this->newLine();
            $this->info("Villages layer: {$villagesLayer->name} (ID: {$villagesLayer->id})");

            $allVillages = DB::select("
                SELECT gf.id, gf.properties->>'name' as name, gf.properties->>'source' as source,
                       ST_GeometryType(gf.geom) as type, ST_AsText(ST_Centroid(gf.geom)) as center,
                       loc.properties->>'name' as locality
                FROM geo_features gf
                LEFT JOIN geo_features loc ON gf.parent_id = loc.id
                WHERE gf.layer_id = ?
                ORDER BY loc.properties->>'name', gf.properties->>'name'
            ", [$villagesLayer->id]);

            $this->info("Total villages in layer: " . count($allVillages));
            foreach (array_slice($allVillages, 0, 20) as $v) {
                $this->line("  {$v->locality} > {$v->name} ({$v->type})");
            }
        }

        // Check OSM places layer
        $placesLayer = DB::selectOne("SELECT id, name FROM map_layers WHERE name = 'City'");
        if ($placesLayer) {
            $this->newLine();
            $this->info("Places layer: {$placesLayer->name} (ID: {$placesLayer->id})");
            $places = DB::select("SELECT count(*) as cnt FROM geo_features gf WHERE gf.layer_id = ?", [$placesLayer->id]);
            $this->info("Count: " . $places[0]->cnt);
        }
    }
}