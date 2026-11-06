<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('app_events', function (Blueprint $blueprint) {
            $blueprint->string('execution_mode')->default('manual')->after('type'); // manual, walking_gps, auto_sum
            $blueprint->string('resolution_method')->default('manual')->after('execution_mode'); // manual, top_contributor, goal_reached
            $blueprint->json('rules')->nullable()->after('resolution_method'); // {"points_per_km": 10, "points_per_ha": 50, etc}
            $blueprint->string('status')->default('active')->after('is_active'); // draft, active, resolved, cancelled
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_events', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['execution_mode', 'resolution_method', 'rules', 'status']);
        });
    }
};
