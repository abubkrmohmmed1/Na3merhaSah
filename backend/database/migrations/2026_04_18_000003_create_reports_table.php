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
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable(); // Foreign Key logic here later
            $table->geometry('location', 'point', 4326); // PostGIS point with SRID 4326
            $table->string('s2_cell_id')->index(); // B-tree index for fast string lookups
            $table->integer('category_id')->nullable();
            // Linking to Address via Spatial Join outcome
            $table->foreignUuid('address_id')->nullable()->constrained('addresses');
            
            // UI Data Contract extensions
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            
            // Workflow State Machine
            $table->enum('status', [
                'started', 
                'govt_review', 
                'surveyor_assigned', 
                'site_visited', 
                'engineering_project', 
                'tender_approved', 
                'site_execution', 
                'admin_approved', 
                'survey_quality', 
                'resolved'
            ])->default('started');

            $table->timestamps();
            $table->softDeletes(); // No hard deleting

            // Native GIST index for spatial column in Laravel usually requires Raw or supported driver functionality.
            // On Laravel 11 with PostgreSQL, spatialIndex is supported.
            $table->spatialIndex('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
