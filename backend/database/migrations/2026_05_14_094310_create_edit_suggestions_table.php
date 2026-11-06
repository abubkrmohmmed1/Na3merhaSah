<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edit_suggestions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('geo_feature_id');
            $table->foreign('geo_feature_id')->references('id')->on('geo_features')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('field'); // name, layer, lat, lng, description
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('status')->default('pending'); // pending, applied, rejected
            $table->integer('confirmations_count')->default(0);
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edit_suggestions');
    }
};
