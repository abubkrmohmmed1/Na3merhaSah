<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Check if uuid-ossp extension is available and if we can use uuid_generate_v4()
$uuidFunction = DB::selectOne("SELECT uuid_generate_v4() as uuid")->uuid ?? null;
if ($uuidFunction) {
    echo "PostgreSQL uuid_generate_v4() is available: $uuidFunction\n";
} else {
    echo "PostgreSQL uuid_generate_v4() is not available or failed.\n";
    // Try to generate UUID using ramsey/uuid if available
    if (class_exists('Ramsey\\Uuid\\Uuid')) {
        $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();
        echo "Ramsey UUID: $uuid\n";
    } else {
        echo "Ramsey UUID not available.\n";
    }
}
