<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('feedback_quality')->nullable(); // ممتاز، جيد، ضعيف
            $table->string('feedback_time')->nullable();    // في الموعد، متأخر، مبكر
            $table->string('feedback_behavior')->nullable(); // محترم، عادي، غير لائق
            $table->string('feedback_cleanliness')->nullable(); // نظيف، مقبول، سيء
            $table->string('feedback_main_issue')->nullable(); // تأخير، سوء تنفيذ، إلخ
            $table->json('feedback_images')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'feedback_quality',
                'feedback_time',
                'feedback_behavior',
                'feedback_cleanliness',
                'feedback_main_issue',
                'feedback_images'
            ]);
        });
    }
};
