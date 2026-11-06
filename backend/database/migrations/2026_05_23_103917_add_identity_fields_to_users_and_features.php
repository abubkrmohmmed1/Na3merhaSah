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
        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_hash')->nullable()->index()->after('national_id');
        });

        Schema::table('geo_features', function (Blueprint $table) {
            $table->string('digital_signature')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('identity_hash');
        });
        Schema::table('geo_features', function (Blueprint $table) {
            $table->dropColumn('digital_signature');
        });
    }
};
