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

        Schema::create('addresses', function (Blueprint $table) use ($isSqlite) {
            $table->uuid('id')->primary();
            $table->string('s2_cell_id')->unique();
            $table->string('address_str')->nullable();
            $table->string('neighborhood')->nullable();
            $table->enum('type', ['residential', 'commercial', 'landmark', 'utility_node', 'other'])->default('residential');
            $table->boolean('is_verified')->default(false);
            $table->json('metadata')->nullable(); 
            if ($isSqlite) {
                // SQLite does not support PostGIS geometry; store as text for unit tests
                $table->string('location')->nullable();
            } else {
                $table->geometry('location', 'point', 4326)->nullable();
            }
            $table->timestamps();

            if (!$isSqlite) {
                $table->spatialIndex('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
