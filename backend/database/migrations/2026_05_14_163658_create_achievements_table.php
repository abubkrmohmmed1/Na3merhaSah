<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->string('icon')->default('🏅');
            $table->string('condition_type'); // add, confirm, vote, flag, streak, points, photos
            $table->integer('condition_value');
            $table->integer('xp_reward')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
