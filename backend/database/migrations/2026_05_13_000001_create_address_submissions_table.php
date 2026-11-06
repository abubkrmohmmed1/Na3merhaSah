<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        Schema::create('address_submissions', function (Blueprint $table) use ($isSqlite) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('address_str');
            $table->string('neighborhood')->nullable();
            $table->enum('type', ['personal', 'service'])->default('personal');
            $table->string('service_layer')->nullable();
            $table->double('lat', 10, 7);
            $table->double('lng', 10, 7);
            if ($isSqlite) {
                $table->string('location')->nullable();
            } else {
                $table->geometry('location', 'point', 4326)->nullable();
            }
            $table->string('s2_cell_id')->nullable();
            $table->string('plus_code')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            if (!$isSqlite) {
                $table->spatialIndex('location');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_submissions');
    }
};