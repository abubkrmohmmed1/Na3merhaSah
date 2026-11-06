<?php

namespace App\Console\Commands;

use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportCityData extends Command
{
    protected $signature = 'map:import-city
        {city : اسم المدينة أو المنطقة (مثال: الخرطوم, ود مدني, القضارف)}
        {--state= : تحديد الولاية (اختياري)}
        {--dry-run : عرض النتائج بدون حفظ}';

    protected $description = 'استيراد جميع الخدمات والمعالم لمدينة كاملة من OpenStreetMap';

    // All POI types we support, mapped to Overpass tags
    private array $poiTypes = [
        'المطاعم' => ['amenity=restaurant', 'amenity=fast_food'],
        'المقاهي' => ['amenity=cafe'],
        'الصيدليات' => ['amenity=pharmacy'],
        'المستشفيات' => ['amenity=hospital', 'amenity=clinic'],
        'البنوك' => ['amenity=bank', 'amenity=atm'],
        'محطات الوقود' => ['amenity=fuel'],
        'المدارس' => ['amenity=school', 'amenity=kindergarten', 'amenity=university'],
        'المخابز' => ['shop=bakery'],
        'البقالات الكبيرة' => ['shop=supermarket'],
        'البقالات الصغيرة' => ['shop=convenience', 'shop=grocery'],
        'الأسواق المركزية' => ['shop=mall'],
        'الفنادق' => ['tourism=hotel', 'tourism=guest_house'],
        'مراكز الشرطة' => ['amenity=police'],
        'مراكز التسوق' => ['shop=mall', 'shop=department_store'],
        'محطات الحافلات' => ['amenity=bus_station', 'highway=bus_stop'],
        'العيادات' => ['amenity=clinic', 'healthcare=clinic'],
        'مراكز الإطفاء' => ['amenity=fire_station'],
        'مكاتب البريد' => ['amenity=post_office'],
        'مواقف السيارات' => ['amenity=parking'],
        'المساجد' => ['amenity=place_of_worship', 'building=mosque'],
        'الرياض والأطفال' => ['leisure=park', 'leisure=playground'],
        'الصالات الرياضية' => ['leisure=sports_centre', 'leisure=fitness_centre'],
    ];

    public function handle()
    {
        $city = $this->argument('city');
        $stateFilter = $this->option('state');
        $dryRun = $this->option('dry-run');

        $this->info("🌍 جلب بيانات مدينة: $city");

        // 1. Get city bounding box from Overpass
        $bbox = $this->getCityBbox($city, $stateFilter);
        if (!$bbox) {
            $this->error("✗ لم أجد المدينة '$city' — تأكد من الاسم (مثال: الخرطوم, ود مدني, القضارف)");
            $this->line('');
            $this->line('المدن المتوقعة: الخرطوم, أم درمان, بحري, ود مدني, القضارف, كسلا, بورتسودان, الأبيض, نيالا, سنار, الدامر, عطبرة, دنقلا, كادوقلي');
            return 1;
        }

        $this->info("  📍 BBOX: {$bbox['south']},{$bbox['west']},{$bbox['north']},{$bbox['east']}");

        $totalImported = 0;
        $totalSkipped = 0;

        // 2. Import admin boundaries if not present
        $this->line('');
        $this->info('🏛️ استيراد الحدود الإدارية...');
        $this->call('map:import-neighborhoods', ['--state' => $city]);

        // 3. Import each POI type
        foreach ($this->poiTypes as $layerName => $tags) {
            $this->line('');
            $this->info("📦 $layerName (" . implode(', ', $tags) . ")");

            // Ensure layer exists
            $layer = MapLayer::firstOrCreate(
                ['name' => $layerName],
                ['description' => 'مستورد من OSM', 'type' => 'points', 'is_public' => true]
            );

            $result = $this->importPoiType($layer, $tags, $bbox, $dryRun);
            $totalImported += $result['imported'];
            $totalSkipped += $result['skipped'];
        }

        $this->newLine(2);
        $this->info("✅ تم استيراد $totalImported معلم" . ($totalSkipped > 0 ? " (تخطي $totalSkipped)" : ""));

        if ($dryRun) {
            $this->warn('⚡ تشغيل تجريبي — لم يتم حفظ أي شيء');
        }

        return 0;
    }

    private function getCityBbox(string $city, ?string $state): ?array
    {
        // First try to find in our database
        $locality = \App\Domains\Spatial\Models\GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
            ->whereRaw("properties->>'name' LIKE ?", ["%$city%"])
            ->first();

        if ($locality && $locality->geom) {
            $bbox = \Illuminate\Support\Facades\DB::selectOne(
                "SELECT ST_XMin(ST_Envelope(geom)) as west, ST_YMin(ST_Envelope(geom)) as south,
                        ST_XMax(ST_Envelope(geom)) as east, ST_YMax(ST_Envelope(geom)) as north
                 FROM geo_features WHERE id = ?", [$locality->id]
            );
            if ($bbox) {
                return ['south' => $bbox->south, 'west' => $bbox->west, 'north' => $bbox->north, 'east' => $bbox->east];
            }
        }

        // Fallback: query Overpass for city boundary
        $query = "[out:json];
        (
          node[\"name\"=\"$city\"][\"place\"~\"city|town\"];
          relation[\"name\"=\"$city\"];
        );
        out body;
        ";

        $response = Http::timeout(30)->asForm()
            ->withHeaders(['User-Agent' => 'NamerhaSah/1.0'])
            ->post('https://overpass-api.de/api/interpreter', ['data' => $query]);

        if ($response->failed()) return null;

        $data = $response->json();
        $elements = $data['elements'] ?? [];
        if (empty($elements)) return null;

        // Return a reasonable bbox for the city
        $el = $elements[0];
        $center = match ($el['type']) {
            'node' => ['lat' => $el['lat'], 'lon' => $el['lon']],
            default => null,
        };

        if ($center) {
            $offset = 0.15; // ~15km
            return [
                'south' => $center['lat'] - $offset,
                'west' => $center['lon'] - $offset,
                'north' => $center['lat'] + $offset,
                'east' => $center['lon'] + $offset,
            ];
        }

        return $center ? [
            'south' => $center['lat'] - 0.15,
            'west' => $center['lon'] - 0.15,
            'north' => $center['lat'] + 0.15,
            'east' => $center['lon'] + 0.15,
        ] : null;
    }

    private function importPoiType($layer, array $tags, array $bbox, bool $dryRun): array
    {
        $imported = 0;
        $skipped = 0;
        $bboxStr = "{$bbox['south']},{$bbox['west']},{$bbox['north']},{$bbox['east']}";

        foreach ($tags as $tag) {
            [$key, $value] = explode('=', $tag);

            $query = "[out:json][timeout:120][bbox:$bboxStr];
            (
              node[\"$key\"=\"$value\"];
              way[\"$key\"=\"$value\"];
            );
            out center;
            ";

            $response = Http::timeout(150)->asForm()
                ->withHeaders(['User-Agent' => 'NamerhaSah/1.0'])
                ->post('https://overpass-api.de/api/interpreter', ['data' => $query]);

            if ($response->failed()) {
                $this->warn("  ↷ Overpass فشل: $tag");
                continue;
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];

            foreach ($elements as $el) {
                if (!isset($el['tags']) || !is_array($el['tags'])) continue;
                $name = null;
                foreach (['name', 'name:ar', 'official_name', 'short_name', 'brand'] as $k) {
                    if (isset($el['tags'][$k]) && is_string($el['tags'][$k]) && !empty($el['tags'][$k])) {
                        $name = $el['tags'][$k];
                        break;
                    }
                }
                if (!$name) continue;

                $lat = $el['lat'] ?? ($el['center']['lat'] ?? null);
                $lon = $el['lon'] ?? ($el['center']['lon'] ?? null);
                if (!$lat || !$lon) continue;

                $osmId = 'osm_' . $el['type'] . '_' . $el['id'];

                // Skip if already imported
                $exists = GeoFeature::where('layer_id', $layer->id)
                    ->whereJsonContains('properties->osm_id', $osmId)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                if (!$dryRun) {
                    GeoFeature::create([
                        'layer_id' => $layer->id,
                        'geometry_wkt' => "POINT($lon $lat)",
                        'properties' => [
                            'osm_id' => $osmId,
                            'name' => $name,
                            'tags' => $el['tags'],
                            'source' => 'osm_city_import',
                        ],
                        'status' => 'verified',
                    ]);
                }
                $imported++;
            }

            $this->info("  ✓ $tag: " . count($elements) . " عنصر, جديد: $imported");
        }

        return compact('imported', 'skipped');
    }
}
