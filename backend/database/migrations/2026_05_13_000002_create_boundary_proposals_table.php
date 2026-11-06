<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        Schema::create('boundary_proposals', function (Blueprint $table) use ($isSqlite) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->enum('proposal_type', ['neighborhood', 'village']);
            $table->string('name');
            $table->double('lat', 10, 7);
            $table->double('lng', 10, 7);
            if ($isSqlite) {
                $table->string('geom')->nullable();
            } else {
                $table->geometry('geom', 'polygon', 4326)->nullable();
            }
            $table->string('s2_cell_id')->nullable();
            $table->string('plus_code')->nullable();
            $table->string('state_id')->nullable();
            $table->string('state_name')->nullable();
            $table->string('locality_id')->nullable();
            $table->string('locality_name')->nullable();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('proposal_type');
            $table->index('user_id');
            if (!$isSqlite) {
                $table->spatialIndex('geom');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boundary_proposals');
    }
};