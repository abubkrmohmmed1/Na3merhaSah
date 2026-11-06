<?php

namespace App\Console\Commands;

use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class ImportCsvData extends Command
{
    protected $signature = 'map:import-csv
        {file : مسار ملف CSV}
        {--layer= : اسم الطبقة (مثال: مطاعم, صيدليات)}
        {--delimiter=, : محدد الأعمدة}
        {--dry-run : عرض النتائج بدون حفظ}
        {--create-layer : إنشاء الطبقة إذا لم توجد}';

    protected $description = 'استيراد نقاط من ملف CSV إلى المخزون الجغرافي';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error('✗ الملف غير موجود: ' . $filePath);
            return 1;
        }

        $layerName = $this->option('layer');
        $delimiter = $this->option('delimiter');
        $dryRun = $this->option('dry-run');
        $createLayer = $this->option('create-layer');

        // Read CSV
        $rows = array_map(fn($line) => str_getcsv($line, $delimiter), file($filePath));
        $header = array_shift($rows);

        if (!$header) {
            $this->error('✗ الملف فارغ أو التنسيق غير صحيح');
            return 1;
        }

        // Detect columns
        $header = array_map('trim', $header);
        $nameCol = array_search('name', $header);
        $latCol = array_search('lat', $header) ?? array_search('latitude', $header);
        $lngCol = array_search('lng', $header) ?? array_search('lon', $header) ?? array_search('longitude', $header);
        $layerCol = array_search('layer', $header) ?? array_search('type', $header) ?? array_search('category', $header);
        $descCol = array_search('description', $header) ?? array_search('desc', $header) ?? array_search('notes', $header);

        $missing = [];
        if ($nameCol === false) $missing[] = 'name';
        if ($latCol === false) $missing[] = 'lat/latitude';
        if ($lngCol === false) $missing[] = 'lng/longitude';
        if (!empty($missing)) {
            $this->error('✗ الأعمدة المطلوبة غير موجودة: ' . implode(', ', $missing));
            $this->line('الأعمدة الموجودة: ' . implode(', ', $header));
            return 1;
        }

        // Get or create layer
        $layer = null;
        if ($layerName) {
            $layer = MapLayer::where('name', $layerName)->first();
            if (!$layer && $createLayer) {
                $layer = MapLayer::create(['name' => $layerName, 'description' => 'مستورد من CSV', 'type' => 'points', 'is_public' => true]);
                $this->info("✓ تم إنشاء الطبقة: $layerName");
            } elseif (!$layer) {
                $this->error("✗ الطبقة '$layerName' غير موجودة. استخدم --create-layer لإنشائها");
                return 1;
            }
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $total = count($rows);

        $this->info("📄 استيراد $total نقطة...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($rows as $i => $row) {
            $row = array_map('trim', $row);
            $name = $row[$nameCol] ?? '';
            $lat = (float) ($row[$latCol] ?? 0);
            $lng = (float) ($row[$lngCol] ?? 0);

            // Determine layer per row if not set globally
            $rowLayer = $layer;
            if (!$rowLayer && $layerCol !== false && !empty($row[$layerCol])) {
                $rowLayer = MapLayer::firstOrCreate(
                    ['name' => $row[$layerCol]],
                    ['description' => 'مستورد من CSV', 'type' => 'points', 'is_public' => true]
                );
            }

            if (empty($name) || $lat == 0 || $lng == 0 || !$rowLayer) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $desc = ($descCol !== false && isset($row[$descCol])) ? $row[$descCol] : null;

            $data = [
                'layer_id' => $rowLayer->id,
                'geometry_wkt' => "POINT($lng $lat)",
                'properties' => array_filter([
                    'name' => $name,
                    'description' => $desc,
                    'source' => 'csv_import',
                    'imported_at' => now()->toDateTimeString(),
                ]),
                'status' => 'verified',
            ];

            if (!$dryRun) {
                try {
                    GeoFeature::create($data);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "الصف " . ($i + 2) . ": $name — " . $e->getMessage();
                }
            } else {
                $imported++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ تم بنجاح: $imported");
        $this->info("⏭️ تم تخطي: $skipped");
        if (!empty($errors)) {
            $this->warn("⚠️ الأخطاء (" . count($errors) . "):");
            foreach (array_slice($errors, 0, 10) as $e) {
                $this->line("  $e");
            }
        }

        if ($dryRun) {
            $this->warn('⚡ تشغيل تجريبي — لم يتم حفظ أي شيء. استخدم php artisan map:import-csv بدون --dry-run للحفظ الفعلي.');
        }

        return 0;
    }
}
