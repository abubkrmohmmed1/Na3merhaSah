<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add surveyor report fields to store the surveyor's findings,
 * images, and decision when they visit the site.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'surveyor_decision')) {
                $table->string('surveyor_decision')->nullable()->after('workflow_metadata');
            }
            if (!Schema::hasColumn('reports', 'surveyor_notes')) {
                $table->text('surveyor_notes')->nullable()->after('surveyor_decision');
            }
            if (!Schema::hasColumn('reports', 'surveyor_area')) {
                $table->decimal('surveyor_area', 10, 2)->nullable()->after('surveyor_notes');
            }
            if (!Schema::hasColumn('reports', 'surveyor_images')) {
                $table->json('surveyor_images')->nullable()->after('surveyor_area');
            }
            if (!Schema::hasColumn('reports', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable();
            }
            if (!Schema::hasColumn('reports', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'surveyor_decision', 'surveyor_notes', 'surveyor_area',
                'surveyor_images', 'first_response_at', 'resolved_at',
            ]);
        });
    }
};
