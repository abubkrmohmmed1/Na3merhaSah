<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('address_submissions', function (Blueprint $table) {
            $table->string('state_id')->nullable()->after('phone');
            $table->string('state_name')->nullable();
            $table->string('locality_id')->nullable();
            $table->string('locality_name')->nullable();
            $table->string('neighborhood_id')->nullable();
            $table->string('neighborhood_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('address_submissions', function (Blueprint $table) {
            $table->dropColumn(['state_id', 'state_name', 'locality_id', 'locality_name', 'neighborhood_id', 'neighborhood_name']);
        });
    }
};