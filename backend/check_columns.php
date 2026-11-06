<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$result = Illuminate\Support\Facades\DB::select('SELECT column_name, data_type FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position', ['geo_features']);
foreach ($result as $row) {
    echo $row->column_name . ': ' . $row->data_type . "\n";
}