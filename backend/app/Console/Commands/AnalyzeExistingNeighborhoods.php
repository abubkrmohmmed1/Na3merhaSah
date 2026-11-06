<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeExistingNeighborhoods extends Command
{
    protected $signature = 'map:analyze-neighborhoods';

    protected $description = 'Analyze existing neighborhoods data';

    public function handle()
    {
        // Get neighborhoods with their locality and state
        $results = DB::select("
            SELECT
                gf.properties->>'name' as neighborhood,
                gf.properties->>'source' as source,
                ST_GeometryType(gf.geom) as geom_type,
                loc.properties->>'name' as locality,
                loc.properties->>'pcode' as loc_pcode,
                st.properties->>'name' as state,
                st.properties->>'pcode' as state_pcode
            FROM geo_features gf
            JOIN map_layers ml ON gf.layer_id = ml.id
            LEFT JOIN geo_features loc ON gf.parent_id = loc.id
            LEFT JOIN geo_features st ON loc.parent_id = st.id
            WHERE ml.name = 'الأحياء'
            ORDER BY st.properties->>'name', loc.properties->>'name', gf.properties->>'name'
        ");

        $byState = [];
        foreach ($results as $r) {
            $state = $r->state ?? 'Unknown';
            $locality = $r->locality ?? 'Unknown';
            if (!isset($byState[$state])) $byState[$state] = [];
            if (!isset($byState[$state][$locality])) $byState[$state][$locality] = [];
            $byState[$state][$locality][] = [
                'name' => $r->neighborhood,
                'type' => $r->geom_type,
                'source' => $r->source,
            ];
        }

        $this->info("Neighborhoods by State/Locality:");
        foreach ($byState as $state => $localities) {
            $total = array_sum(array_map('count', $localities));
            $this->line("  [$state] ($total neighborhoods)");
            foreach ($localities as $loc => $hoods) {
                $this->line("    - $loc (" . count($hoods) . "): " . implode(', ', array_column($hoods, 'name')));
            }
        }

        $this->newLine();
        $this->info("Total: " . count($results));

        // Check if any neighborhoods are in Khartoum, Gezira, or Gedaref states
        $targetStates = ['الخرطوم', 'الجزيرة', 'القضارف'];
        $this->newLine();
        $this->info("Target states analysis:");
        foreach ($targetStates as $target) {
            $count = 0;
            $localities = [];
            foreach ($byState as $state => $locs) {
                if (str_contains($state, $target) || $target === $state) {
                    $count += array_sum(array_map('count', $locs));
                    $localities = array_merge($localities, array_keys($locs));
                }
            }
            $this->line("  $target: $count neighborhoods in " . count(array_unique($localities)) . " localities");
        }
    }
}