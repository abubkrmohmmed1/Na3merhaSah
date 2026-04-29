<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportSeeder extends Seeder
{
    /**
     * Seed realistic engineering incident reports across different neighborhoods and statuses.
     */
    public function run(): void
    {
        // Fetch existing addresses to link reports to
        $addresses = DB::table('addresses')->get();

        if ($addresses->isEmpty()) {
            $this->command->warn('⚠️ لا توجد عناوين! شغّل AddressSeeder أولاً.');
            return;
        }

        $reports = [
            [
                'description' => 'تسرب مياه كبير في الشارع الرئيسي يعيق حركة المرور',
                'category_id' => 1, // مياه
                'status' => 'started',
            ],
            [
                'description' => 'انقطاع كهرباء متكرر في المنطقة منذ 3 أيام',
                'category_id' => 2, // كهرباء
                'status' => 'started',
            ],
            [
                'description' => 'حفرة عميقة في الطريق تشكل خطراً على السيارات',
                'category_id' => 3, // طرق
                'status' => 'surveyor_assigned',
            ],
            [
                'description' => 'انسداد في مجرى الصرف الصحي يسبب فيضان',
                'category_id' => 4, // صرف صحي
                'status' => 'site_visited',
            ],
            [
                'description' => 'أعمدة إنارة محطمة في الشارع الفرعي',
                'category_id' => 2, // كهرباء
                'status' => 'engineering_phase',
            ],
            [
                'description' => 'تصدع في جدار المبنى الحكومي بسبب الأمطار',
                'category_id' => 5, // مباني
                'status' => 'bidding_phase',
            ],
            [
                'description' => 'ماسورة مكسورة تهدر المياه منذ أسبوع',
                'category_id' => 1, // مياه
                'status' => 'execution',
            ],
            [
                'description' => 'تشققات في الأسفلت بعد موسم الأمطار',
                'category_id' => 3, // طرق
                'status' => 'resolved',
            ],
            [
                'description' => 'غطاء بالوعة مفقود يشكل خطراً على المشاة',
                'category_id' => 4, // صرف صحي
                'status' => 'started',
            ],
            [
                'description' => 'تسرب غاز بالقرب من المدرسة يحتاج تدخل عاجل',
                'category_id' => 6, // طوارئ
                'status' => 'started',
            ],
            [
                'description' => 'انهيار جزئي في رصيف المشاة',
                'category_id' => 3, // طرق
                'status' => 'surveyor_assigned',
            ],
            [
                'description' => 'عطل في محول كهربائي يؤثر على 50 منزل',
                'category_id' => 2, // كهرباء
                'status' => 'site_visited',
            ],
        ];

        foreach ($reports as $index => $report) {
            // Distribute reports across addresses
            $address = $addresses[$index % $addresses->count()];

            DB::table('reports')->insert([
                'id' => Str::uuid(),
                'user_id' => Str::uuid(), // Simulated citizen
                'address_id' => $address->id,
                'location' => DB::raw("(SELECT location FROM addresses WHERE id = '{$address->id}')"),
                's2_cell_id' => $address->s2_cell_id,
                'plus_code' => $address->plus_code,
                'description' => $report['description'],
                'category_id' => $report['category_id'],
                'images' => json_encode([
                    'https://placehold.co/400x300/e2e8f0/1e293b?text=بلاغ+' . ($index + 1),
                ]),
                'status' => $report['status'],
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ تم إدخال 12 بلاغ وهمي بحالات متنوعة (من started إلى resolved)');
    }
}
