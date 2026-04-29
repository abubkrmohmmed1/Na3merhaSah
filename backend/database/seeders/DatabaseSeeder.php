<?php

namespace Database\Seeders;

use App\Models\User;
use App\Domains\Addressing\Models\Address;
use App\Domains\Reporting\Models\Report;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Call standard seeders
        $this->call([
            AddressSeeder::class,
            ReportSeeder::class,
            AdminDashboardSeeder::class,
        ]);

        // Create an Admin User
        User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Admin User',
            'phone' => '0000000000', // Dummy phone
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Password 'password'
            // 'is_admin' => true, // Uncomment if you add an is_admin field to User model
        ]);

        // 2. إنشاء مستخدمين تجريبيين
        $users = [
            ['name' => 'محمد أحمد', 'phone' => '0912345678', 'email' => 'mohamed@example.com'],
            ['name' => 'سارة علي', 'phone' => '0123456789', 'email' => 'sara@example.com'],
        ];

        foreach ($users as $userData) {
            $uId = (string) Str::uuid();
            $aId = (string) Str::uuid();
            $rId = (string) Str::uuid();

            User::create([
                'id' => $uId,
                'name' => $userData['name'],
                'phone' => $userData['phone'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
            ]);

            // 2. إضافة عنوان مسكن للمستخدم
            $address = Address::create([
                'id' => $aId,
                's2_cell_id' => 's2_' . Str::random(7),
                'address_str' => 'عنوان تجريبي لـ ' . $userData['name'],
                'neighborhood' => 'حي تجريبي',
                'location' => 'SRID=4326;POINT(32.5 15.6)',
            ]);

            User::where('id', $uId)->update(['home_address_id' => $aId]);

            // 3. إنشاء بلاغ مرتبط بهذا المستخدم وهذا العنوان
            Report::create([
                'id' => $rId,
                'user_id' => $uId,
                'address_id' => $aId,
                'location' => 'SRID=4326;POINT(32.5 15.6)',
                's2_cell_id' => $address->s2_cell_id,
                'description' => 'بلاغ تجريبي من المستخدم ' . $userData['name'],
                'category_id' => 1,
                'status' => 'started',
                'workflow_step' => 'location_selection',
            ]);
        }
    }
}
