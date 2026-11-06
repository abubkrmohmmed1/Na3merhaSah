<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$result = Illuminate\Support\Facades\DB::select("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'geo_features' AND column_name = 'id'");
print_r($result[0]);

echo "\n\nFull table structure:\n";
$columns = Illuminate\Support\Facades\DB::select("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'geo_features' ORDER BY ordinal_position");
foreach ($columns as $col) {
    echo str_pad($col->column_name, 20) . " | " .
         str_pad($col->data_type, 15) . " | " .
         str_pad($col->is_nullable, 10) . " | " .
         ($col->column_default ?? 'NULL') . "\n";
}