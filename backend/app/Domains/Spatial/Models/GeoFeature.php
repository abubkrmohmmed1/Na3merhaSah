<?php

namespace App\Domains\Spatial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Domains\Spatial\Services\S2GeometryService;

class GeoFeature extends Model
{
    use HasUuids;

    protected $fillable = [
        'layer_id',
        'user_id',
        'parent_id',
        's2_cell_id',
        'plus_code',
        'geom',
        'properties',
        'status',
        'latitude',
        'longitude',
        'geometry_wkt'
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($feature) {
            $lat = $feature->getAttribute('latitude');
            $lng = $feature->getAttribute('longitude');
            $wkt = $feature->getAttribute('geometry_wkt');

            if ($wkt) {
                // Validate WKT to prevent SQL Injection
                if (!preg_match('/^[a-z0-9\(\)\s,\.\-\+]+$/i', trim($wkt))) {
                    throw new \InvalidArgumentException('Invalid WKT geometry format');
                }
                $cleanWkt = trim($wkt);
                $feature->geom = DB::raw("ST_GeomFromText('$cleanWkt', 4326)");
                $point = DB::selectOne("SELECT ST_AsText(ST_Centroid(ST_GeomFromText(?, 4326))) as center", [$cleanWkt]);
                if ($point && preg_match('/POINT\((.*) (.*)\)/', $point->center, $matches)) {
                    $lng = $matches[1];
                    $lat = $matches[2];
                }
            }

            if ($lat !== null && $lng !== null) {
                $s2Service = app(S2GeometryService::class);
                $feature->s2_cell_id = $s2Service->latLngToToken((float) $lat, (float) $lng);
                $feature->plus_code = $s2Service->generatePlusCode((float) $lat, (float) $lng);

                if (!$wkt) {
                    $feature->geom = DB::raw(sprintf(
                        "ST_SetSRID(ST_MakePoint(%F, %F), 4326)",
                        (float) $lng,
                        (float) $lat
                    ));
                }
            }

            // Always unset virtual columns so they never leak to SQL insert/update
            unset($feature->attributes['latitude']);
            unset($feature->attributes['longitude']);
            unset($feature->attributes['geometry_wkt']);

            // محاولة كشف الأب تلقائياً قبل الحفظ
            if (!$feature->parent_id && $feature->geom) {
                $feature->autoDetectParent();
            }
        });
    }

    public function mapLayer(): BelongsTo
    {
        return $this->belongsTo(MapLayer::class, 'layer_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(GeoFeature::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(GeoFeature::class, 'parent_id');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function autoDetectParent(): void
    {
        $layerName = $this->mapLayer?->name;
        $parentLayerName = match($layerName) {
            'المحليات' => 'الولايات',
            'الأحياء' => 'المحليات',
            default => 'الأحياء'
        };

        $parentLayerId = MapLayer::where('name', $parentLayerName)->value('id');
        if (!$parentLayerId) return;

        // لتجنب خطأ تحويل الـ Expression إلى string، نستخدم المعامل المكاني مباشرة في الـ raw
        // وبما أننا في مرحلة saving، الـ geom قد يكون DB::raw
        $geomSql = $this->geom instanceof \Illuminate\Database\Query\Expression 
            ? $this->geom->getValue(DB::connection()->getQueryGrammar()) 
            : "'$this->geom'";

        $parent = GeoFeature::where('layer_id', $parentLayerId)
            ->where('id', '!=', $this->id)
            ->whereNotNull('geom')
            ->whereRaw("ST_Contains(geom, ST_Centroid($geomSql))")
            ->orderByRaw("ST_Area(geom) ASC")
            ->first();

        if ($parent) {
            $this->parent_id = $parent->id;
        }
    }
}
