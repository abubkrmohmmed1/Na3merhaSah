<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_events', function (Blueprint $table) {
            if (!Schema::hasColumn('app_events', 'scope_type')) {
                $table->string('scope_type')->default('national');
            }
            if (!Schema::hasColumn('app_events', 'scope_id')) {
                $table->uuid('scope_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_events', function (Blueprint $table) {
            if (Schema::hasColumn('app_events', 'scope_type')) {
                $table->dropColumn('scope_type');
            }
            if (Schema::hasColumn('app_events', 'scope_id')) {
                $table->dropColumn('scope_id');
            }
        });
    }
};
