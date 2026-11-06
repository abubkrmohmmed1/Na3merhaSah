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
        Schema::table('geo_features', function (Blueprint $table) {
            $table->integer('confidence_score')->default(0)->after('status');
            $table->string('verification_level')->nullable()->after('confidence_score'); // OSM, Auto, Owner, Field, Delivery
            $table->timestamp('verified_at')->nullable()->after('verification_level');
            $table->uuid('verified_by')->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geo_features', function (Blueprint $table) {
            $table->dropColumn(['confidence_score', 'verification_level', 'verified_at', 'verified_by']);
        });
    }
};
