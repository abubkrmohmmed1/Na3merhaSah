<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetAdminPassword extends Command
{
    protected $signature = 'admin:set-password {email} {password}';
    protected $description = 'Set a new password for an admin user';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('User not found!');
            return;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password updated successfully for $email");
    }
}
