<?php

namespace App\Console\Commands;

use App\Domains\Spatial\Services\ConfidenceEngine;
use Illuminate\Console\Command;

class RunConfidenceMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'map:update-confidence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update confidence scores for all map features based on user signals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Confidence Engine maintenance...');
        ConfidenceEngine::runMaintenance();
        $this->success('✅ Confidence scores updated successfully!');
    }
}
