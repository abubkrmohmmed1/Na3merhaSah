<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkGeziraVillages extends Command
{
    protected $signature = 'map:link-gezira-villages {--dry-run}';

    protected $description = 'Link villages to Gezira localities via spatial containment';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $gedeira = DB::selectOne("
            SELECT gf.id, gf.properties->>'name' as name
            FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'الولايات' AND gf.properties->>'pcode' = 'SD15'
        ");

        if (!$gedeira) {
            $this->error("Gezira not found");
            return;
        }

        $this->info("Gezira: {$gedeira->name}");
        $this->info($dryRun ? "DRY RUN" : "LIVE RUN");

        // Get Gezira localities
        $localities = DB::select("
            SELECT gf.id, gf.properties->>'name' as name, gf.geom
            FROM geo_features gf
            WHERE parent_id = ?
            ORDER BY gf.properties->>'name'
        ", [$gedeira->id]);

        $this->info("Localities: " . count($localities));

        // Get villages layer
        $villagesLayerId = DB::selectOne("SELECT id FROM map_layers WHERE name = 'القرى والمدن'");
        if (!$villagesLayerId) {
            $this->error("Villages layer not found");
            return;
        }

        // Smart approach: single spatial query for ALL villages
        $this->info("Finding villages inside Gezira localities...");
        $matches = DB::select("
            SELECT v.id as village_id, v.properties->>'name' as village_name,
                   l.id as locality_id, l.properties->>'name' as locality_name,
                   ST_Contains(l.geom, ST_Centroid(v.geom)) as contains
            FROM geo_features v
            JOIN geo_features l ON ST_Contains(l.geom, ST_Centroid(v.geom))
            WHERE v.layer_id = ? AND l.parent_id = ?
        ", [$villagesLayerId->id, $gedeira->id]);

        $this->info("Spatial matches: " . count($matches));

        $byLocality = [];
        foreach ($localities as $loc) {
            $byLocality[$loc->id] = ['name' => $loc->name, 'count' => 0];
        }

        if ($dryRun) {
            foreach ($matches as $m) {
                $this->line("  {$m->locality_name} > {$m->village_name}");
                $byLocality[$m->locality_id]['count']++;
            }
        } else {
            $bar = $this->output->createProgressBar(count($matches));
            $bar->start();

            foreach ($matches as $m) {
                DB::update("UPDATE geo_features SET parent_id = ? WHERE id = ?", [$m->locality_id, $m->village_id]);
                $byLocality[$m->locality_id]['count']++;
                $bar->advance();
            }

            $bar->finish();
        }

        $this->newLine();
        $this->info("Villages per locality:");
        $total = 0;
        foreach ($byLocality as $locId => $data) {
            $this->line("  {$data['name']}: {$data['count']}");
            $total += $data['count'];
        }
        $this->line("  TOTAL LINKED: $total");

        // Also link villages that are in Gezira state but not in a locality
        $this->newLine();
        $unlinked = DB::select("
            SELECT count(*) as cnt FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'القرى والمدن' AND gf.parent_id IS NULL
        ");
        $this->info("Unlinked villages (no parent): {$unlinked[0]->cnt}");

        // Show sample of villages in Gezira that aren't matched
        $outside = DB::select("
            SELECT gf.properties->>'name' as name, ST_AsText(ST_Centroid(gf.geom)) as center,
                   ST_Contains(gz.geom, ST_Centroid(gf.geom)) as inside_gezira
            FROM geo_features gf
            CROSS JOIN geo_features gz
            JOIN map_layers ml ON gf.layer_id = ml.id
            JOIN map_layers ml2 ON gz.layer_id = ml2.id
            WHERE ml.name = 'القرى والمدن' AND ml2.name = 'الولايات' AND gz.properties->>'pcode' = 'SD15'
            LIMIT 10
        ");

        $inGeziraCount = 0;
        foreach ($outside as $o) {
            if ($o->inside_gezira) $inGeziraCount++;
        }
        $this->info("Sample: $inGeziraCount of first 10 villages are inside Gezira");
    }
}