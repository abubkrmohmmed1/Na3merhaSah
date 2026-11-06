<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        Schema::create('app_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['competition', 'challenge', 'community', 'announcement'])->default('announcement');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->integer('reward_points')->default(0);
            $table->string('target_layer')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // إضافة العمود المكاني (PostGIS) — not supported on SQLite
        if (!$isSqlite) {
            DB::statement('ALTER TABLE app_events ADD COLUMN geom geometry(Point, 4326)');
            DB::statement('CREATE INDEX app_events_geom_idx ON app_events USING GIST (geom)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_events');
    }
};
