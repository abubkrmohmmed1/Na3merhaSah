<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Domains\Auth\Models\User;

echo "=== Token Diagnostic ===\n";

$tokenCount = DB::table('personal_access_tokens')->count();
echo "Total tokens in DB: $tokenCount\n";

// Check tokenable_id types
$samples = DB::table('personal_access_tokens')->take(5)->get();
foreach ($samples as $t) {
    echo "Token #{$t->id}: tokenable_id='{$t->tokenable_id}' (type: " . gettype($t->tokenable_id) . ")\n";
}

// Check user IDs
$users = User::take(3)->get();
foreach ($users as $u) {
    echo "User {$u->name}: id='{$u->id}' (type: " . gettype($u->id) . ")\n";
}

echo "\n=== FIX: Deleting all personal access tokens ===\n";
$deleted = DB::table('personal_access_tokens')->delete();
echo "Deleted $deleted tokens\n";
echo "Done! Users need to log in again.\n";
