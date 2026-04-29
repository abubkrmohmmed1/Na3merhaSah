<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * PERF-04: Add missing database indexes for frequently queried columns.
 * These indexes dramatically improve query performance for dashboard stats,
 * report listings, and user lookups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->index('status', 'idx_reports_status');
            $table->index('user_id', 'idx_reports_user_id');
            $table->index('category_id', 'idx_reports_category_id');
            $table->index('created_at', 'idx_reports_created_at');
        });

        // Trigram index for fuzzy text search on addresses (requires pg_trgm extension)
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_addresses_address_str_trgm ON addresses USING gin (address_str gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_addresses_neighborhood_trgm ON addresses USING gin (neighborhood gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('idx_reports_status');
            $table->dropIndex('idx_reports_user_id');
            $table->dropIndex('idx_reports_category_id');
            $table->dropIndex('idx_reports_created_at');
        });

        DB::statement('DROP INDEX IF EXISTS idx_addresses_address_str_trgm');
        DB::statement('DROP INDEX IF EXISTS idx_addresses_neighborhood_trgm');
    }
};
