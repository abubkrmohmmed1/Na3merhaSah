<?php

namespace Database\Seeders;

use App\Domains\Reporting\Models\Report;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminDashboardSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 30));
            
            Report::create([
                'id' => Str::uuid(),
                'user_id' => null,
                'location' => "SRID=4326;POINT(32.5 15.5)",
                's2_cell_id' => 'simulated_s2_cell_' . $i,
                'category_id' => rand(1, 6),
                'description' => 'بلاغ تجريبي رقم ' . $i,
                'status' => (rand(0, 1) == 1) ? Report::STATUS_RESOLVED : Report::STATUS_STARTED,
                'workflow_step' => Report::STEP_REVIEW,
                'created_at' => $createdAt,
                'first_response_at' => $createdAt->copy()->addHours(rand(1, 24)),
                'resolved_at' => (rand(0, 1) == 1) ? $createdAt->copy()->addDays(rand(1, 5)) : null,
            ]);
        }
    }
}
