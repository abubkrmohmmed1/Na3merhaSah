<?php

// Bootstrap Laravel to create admin user
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@example.com')->first();

if ($user) {
    $user->role = 'admin';
    $user->save();
    echo "Updated existing user to admin\n";
    echo "  Email: {$user->email}\n";
    echo "  Phone: {$user->phone}\n";
    echo "  Role: {$user->role}\n";
    echo "  Password: password (unchanged)\n";
} else {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@namerhsah.sd',
        'phone' => '0900000000',
        'role' => 'admin',
        'password' => Hash::make('Admin@2026'),
    ]);
    echo "Created new admin user\n";
    echo "  Email: {$user->email}\n";
    echo "  Phone: {$user->phone}\n";
    echo "  Role: {$user->role}\n";
    echo "  Password: Admin@2026\n";
}
