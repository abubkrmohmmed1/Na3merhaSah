<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('geo_feature_id')->nullable();
            $table->foreign('geo_feature_id')->references('id')->on('geo_features')->cascadeOnDelete();
            $table->string('action_type'); // confirm, flag, vote, add
            $table->json('payload')->nullable(); // {reason, rating, description}
            $table->integer('points')->default(0);
            $table->string('status')->default('active'); // active, revoked
            $table->timestamps();

            $table->index(['user_id', 'action_type']);
            $table->index(['geo_feature_id', 'action_type']);
            $table->unique(['user_id', 'geo_feature_id', 'action_type'], 'user_action_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_actions');
    }
};
