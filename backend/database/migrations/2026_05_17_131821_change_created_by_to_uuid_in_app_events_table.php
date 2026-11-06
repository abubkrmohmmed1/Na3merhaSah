<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // تغيير نوع العمود created_by إلى UUID ليتوافق مع جدول المستخدمين
        Schema::table('app_events', function (Blueprint $table) {
            $table->uuid('created_by_uuid')->nullable();
        });

        // نقل البيانات إذا وجدت (رغم أن الجدول غالباً فارغ بسبب الخطأ)
        // DB::statement('UPDATE app_events SET created_by_uuid = created_by::uuid WHERE created_by IS NOT NULL');

        Schema::table('app_events', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('app_events', function (Blueprint $table) {
            $table->renameColumn('created_by_uuid', 'created_by');
        });
    }

    public function down(): void
    {
        Schema::table('app_events', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by_old')->nullable();
        });
        
        Schema::table('app_events', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('app_events', function (Blueprint $table) {
            $table->renameColumn('created_by_old', 'created_by');
        });
    }
};
