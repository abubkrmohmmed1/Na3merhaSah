<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite does not support PostGIS or information_schema queries — skip for unit tests
            return;
        }

        // نتحقق أولاً إذا كان العمود غير موجود قبل إضافته (للأمان بين الـ Local والسيرفر)
        $hasGeom = DB::selectOne("
            SELECT count(*) 
            FROM information_schema.columns 
            WHERE table_name = 'app_events' AND column_name = 'geom'
        ")->count > 0;

        if (!$hasGeom) {
            // إضافة العمود المكاني
            DB::statement('ALTER TABLE app_events ADD COLUMN geom geometry(Point, 4326)');
            DB::statement('CREATE INDEX IF NOT EXISTS app_events_geom_idx ON app_events USING GIST (geom)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('app_events', function (Blueprint $table) {
                $table->dropColumn('geom');
            });
        }
    }
};
