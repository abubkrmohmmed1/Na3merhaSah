<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Re-creating necessary address performance indexes that were inside the deleted reports index migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Trigram index for fuzzy text search on addresses (requires pg_trgm extension)
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_addresses_address_str_trgm ON addresses USING gin (address_str gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_addresses_neighborhood_trgm ON addresses USING gin (neighborhood gin_trgm_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_addresses_address_str_trgm');
        DB::statement('DROP INDEX IF EXISTS idx_addresses_neighborhood_trgm');
    }
};
