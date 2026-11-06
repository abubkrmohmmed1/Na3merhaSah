<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\Addressing\Models\Address;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AddressSeeder::class,
            AdminUserSeeder::class,
        ]);

        $users = [
            ['name' => 'محمد أحمد', 'phone' => '0912345678', 'email' => 'mohamed@example.com'],
            ['name' => 'سارة علي', 'phone' => '0123456789', 'email' => 'sara@example.com'],
        ];

        foreach ($users as $userData) {
            $uId = (string) Str::uuid();
            $aId = (string) Str::uuid();

            User::create([
                'id' => $uId,
                'name' => $userData['name'],
                'phone' => $userData['phone'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
            ]);

            Address::create([
                'id' => $aId,
                's2_cell_id' => 's2_' . Str::random(7),
                'address_str' => 'عنوان تجريبي لـ ' . $userData['name'],
                'neighborhood' => 'حي تجريبي',
                'location' => 'SRID=4326;POINT(32.5 15.6)',
            ]);

            User::where('id', $uId)->update(['home_address_id' => $aId]);
        }
    }
}