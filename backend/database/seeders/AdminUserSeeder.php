<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user from environment variables.
     *
     * Required .env keys:
     *   ADMIN_EMAIL    — admin login email
     *   ADMIN_PASSWORD — admin login password
     *   ADMIN_PHONE    — admin phone number (optional, defaults to 0000000000)
     */
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@namerhsah.sd');
        $password = env('ADMIN_PASSWORD');
        $phone    = env('ADMIN_PHONE', '0000000000');

        if (empty($password)) {
            $this->command->error('ADMIN_PASSWORD is not set in .env — skipping admin seeder.');
            return;
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            $existing->role = 'admin';
            $existing->save();
            $this->command->info("Updated existing user '{$email}' to admin role.");
            return;
        }

        User::create([
            'id'       => (string) Str::uuid(),
            'name'     => 'Admin',
            'email'    => $email,
            'phone'    => $phone,
            'role'     => 'admin',
            'password' => Hash::make($password),
        ]);

        $this->command->info("Admin user '{$email}' created successfully.");
    }
}
