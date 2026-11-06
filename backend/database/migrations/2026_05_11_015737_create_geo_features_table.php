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
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        Schema::create('geo_features', function (Blueprint $table) use ($isSqlite) {
            $table->uuid('id')->primary();
            $table->foreignUuid('layer_id')->constrained('map_layers')->onDelete('cascade');
            if ($isSqlite) {
                // SQLite does not support PostGIS geometry; store as text for unit tests
                $table->string('geom')->nullable();
            } else {
                $table->geometry('geom', 'point', 4326);
            }
            $table->string('s2_cell_id')->index();
            $table->string('plus_code')->nullable()->index();
            $table->json('properties')->nullable();
            $table->boolean('is_private')->default(false)->index();
            $table->string('status')->default('pending'); // pending, verified, rejected
            $table->timestamps();
        });

        if (!$isSqlite) {
            Schema::table('geo_features', function (Blueprint $table) {
                $table->spatialIndex('geom');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_features');
    }
};
