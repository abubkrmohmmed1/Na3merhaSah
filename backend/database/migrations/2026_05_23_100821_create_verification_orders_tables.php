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
        Schema::create('verification_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->string('reference_no')->unique(); // Order #1234
            $table->string('status')->default('pending_payment'); // pending_payment, processing, completed, cancelled
            $table->integer('total_items')->default(0);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->string('currency')->default('SDG');
            $table->string('payment_method')->nullable(); // bankak, cashi, bravo
            $table->json('payment_meta')->nullable(); // transaction_id, etc.
            $table->json('meta')->nullable(); // Order requirements
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::create('verification_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->index();
            $table->uuid('geo_feature_id')->nullable()->index(); // If existing
            $table->string('target_name')->nullable();
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->string('target_geom')->nullable();
            } else {
                $table->geometry('target_geom', 'point', 4326)->nullable();
            }
            $table->string('status')->default('pending'); // pending, assigned, verified, rejected
            $table->uuid('verified_by')->nullable(); // User who verified it
            $table->json('result_data')->nullable(); // Proof, photos, etc
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('verification_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_items');
        Schema::dropIfExists('verification_orders');
    }
};
