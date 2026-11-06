<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTokens extends Command
{
    protected $signature = 'tokens:clear';
    protected $description = 'Clear all personal access tokens (forces re-login)';

    public function handle(): void
    {
        $count = DB::table('personal_access_tokens')->count();
        DB::table('personal_access_tokens')->delete();
        $this->info("Deleted {$count} personal access tokens. Users must log in again.");
    }
}
