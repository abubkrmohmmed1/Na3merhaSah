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
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('api_key_id');
            $table->string('endpoint');
            $table->string('method');
            $table->integer('status_code');
            $table->integer('response_time_ms')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->json('payload_summary')->nullable(); // Count of items or key params
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('cascade');
            
            $table->index(['company_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_usage_logs');
    }
};
