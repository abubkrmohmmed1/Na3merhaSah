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
        // Raw ALTER only works on PostgreSQL/PostGIS — skip on SQLite (unit tests)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Illuminate\Support\Facades\DB::statement('ALTER TABLE geo_features ALTER COLUMN geom TYPE geometry(GEOMETRY, 4326) USING geom::geometry');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Illuminate\Support\Facades\DB::statement('ALTER TABLE geo_features ALTER COLUMN geom TYPE geometry(POINT, 4326) USING ST_Centroid(geom)');
        }
    }
};
