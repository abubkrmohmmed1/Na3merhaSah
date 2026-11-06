<?php

namespace App\Domains\Spatial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MapLayer extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_public',
        'icon',
        'color',
    ];

    public function geoFeatures(): HasMany
    {
        return $this->hasMany(GeoFeature::class, 'layer_id');
    }
}
