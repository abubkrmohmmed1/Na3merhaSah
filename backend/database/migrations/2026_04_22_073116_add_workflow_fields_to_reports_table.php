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
        Schema::table('reports', function (Blueprint $table) {
            $table->enum('workflow_step', ['location_selection', 'category_selection', 'description_input', 'image_upload', 'review_submit'])->default('location_selection')->after('status');
            $table->json('workflow_metadata')->nullable()->after('workflow_step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['workflow_step', 'workflow_metadata']);
        });
    }
};
