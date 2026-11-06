<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Spatial\Models\GeoFeature;

class DebugLocalityProperties extends Command
{
    protected $signature = 'map:debug-localities';

    protected $description = 'Debug locality properties structure';

    public function handle()
    {
        $samples = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
            ->limit(5)
            ->get();

        foreach ($samples as $f) {
            $this->line("ID: {$f->id}");
            $this->line("Properties: " . json_encode($f->properties, JSON_UNESCAPED_UNICODE));
            $this->line("Status: {$f->status}");
            $this->line("Parent ID: " . ($f->parent_id ?? 'null'));
            $this->line("---");
        }

        $this->newLine();

        // Check Administrative layer
        $admin = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'Administrative'))->limit(3)->get();
        $this->info("Administrative layer samples:");
        foreach ($admin as $f) {
            $this->line("  ID: {$f->id}");
            $this->line("  Properties: " . json_encode($f->properties, JSON_UNESCAPED_UNICODE));
            $this->line("  Status: {$f->status}");
            $this->line("  ---");
        }
    }
}