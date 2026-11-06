<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_events', function (Blueprint $table) {
            $table->string('target_action', 50)->nullable()->after('target_layer');
        });
    }

    public function down(): void
    {
        Schema::table('app_events', function (Blueprint $table) {
            $table->dropColumn('target_action');
        });
    }
};
