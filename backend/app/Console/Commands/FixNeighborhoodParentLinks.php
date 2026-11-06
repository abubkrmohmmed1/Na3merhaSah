<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixNeighborhoodParentLinks extends Command
{
    protected $signature = 'map:fix-neighborhood-links';

    protected $description = 'Fix parent_id linking for neighborhoods based on spatial containment';

    public function handle()
    {
        $this->info("Finding neighborhoods with missing parent links...");

$orphans = DB::select("
            SELECT gf.id, gf.properties->>'name' as name, ST_GeometryType(gf.geom) as type
            FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'الأحياء' AND gf.parent_id IS NULL
        ");

        $this->info("Orphan neighborhoods: " . count($orphans));

        if (count($orphans) === 0) {
            $this->info("No orphans to fix.");
            return;
        }

        $this->newLine();
        $this->info("Sample orphans:");
        foreach (array_slice($orphans, 0, 10) as $o) {
            $this->line("  - {$o->name} ({$o->type})");
        }

        $this->newLine();
        $this->info("Attempting to link via spatial containment...");

        $bar = $this->output->createProgressBar(count($orphans));
        $bar->start();

        $linked = 0;
        $notFound = 0;

        foreach ($orphans as $orphan) {
            $centroid = DB::selectOne("
                SELECT ST_X(ST_Centroid(geom)) as lng, ST_Y(ST_Centroid(geom)) as lat
                FROM geo_features WHERE id = ?
            ", [$orphan->id]);

            if (!$centroid) {
                $notFound++;
                $bar->advance();
                continue;
            }

            $result = DB::selectOne("
                SELECT loc.id
                FROM geo_features loc
                JOIN map_layers ml ON loc.layer_id = ml.id
                WHERE ml.name = 'المحليات' AND loc.geom IS NOT NULL
                AND ST_Contains(loc.geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))
                ORDER BY ST_Area(loc.geom) ASC
                LIMIT 1
            ", [$centroid->lng, $centroid->lat]);

            if ($result && $result->id) {
                DB::update("UPDATE geo_features SET parent_id = ? WHERE id = ?", [$result->id, $orphan->id]);
                $linked++;
            } else {
                $notFound++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Linked: $linked, Not found: $notFound");

        // Verify
        $this->newLine();
        $this->info("Verification:");
        $linkedNow = DB::selectOne("
            SELECT count(*) as cnt FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            WHERE ml.name = 'الأحياء' AND gf.parent_id IS NOT NULL
        ");
        $this->info("Neighborhoods with parent: " . $linkedNow->cnt);

        // Check by state
        $byState = DB::select("
            SELECT
                st.properties->>'name' as state,
                count(*) as cnt
            FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            JOIN geo_features loc ON gf.parent_id = loc.id
            JOIN geo_features st ON loc.parent_id = st.id
            WHERE ml.name = 'الأحياء'
            GROUP BY st.properties->>'name'
            ORDER BY cnt DESC
        ");

        foreach ($byState as $row) {
            $this->line("  {$row->state}: {$row->cnt} neighborhoods");
        }
    }
}