<?php
$layers = \App\Domains\Spatial\Models\MapLayer::whereNotIn('name', ['الولايات', 'المحليات', 'الأحياء', 'الحدود الإدارية'])->get();
foreach ($layers as $l) {
    echo "ID: " . $l->id . " | Name: " . $l->name . PHP_EOL;
}