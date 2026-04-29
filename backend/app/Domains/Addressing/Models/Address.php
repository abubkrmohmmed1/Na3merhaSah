<?php

namespace App\Domains\Addressing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Address extends Model
{
    use HasUuids;

    // أنواع العناوين
    const TYPE_RESIDENTIAL = 'residential';
    const TYPE_COMMERCIAL = 'commercial';
    const TYPE_LANDMARK = 'landmark';
    const TYPE_UTILITY_NODE = 'utility_node';
    const TYPE_OTHER = 'other';

    protected $fillable = [
        'id',
        's2_cell_id',
        'plus_code', // إضافة حقل plus_code الجديد
        'address_str',
        'neighborhood',
        'location',
        'type',
        'is_verified',
        'metadata'
    ];

    /**
     * Since Laravel 11, basic spatial casts or raw DB inserts are used.
     */
    protected $casts = [
        'metadata' => 'array',
        'is_verified' => 'boolean',
    ];

    public function scopeWithCoordinates($query)
    {
        return $query->selectRaw('*, ST_Y(location::geometry) as location_lat, ST_X(location::geometry) as location_lng');
    }

    public function getLocationLatAttribute()
    {
        if (array_key_exists('location_lat', $this->attributes)) {
            return $this->attributes['location_lat'];
        }
        return \DB::selectOne("SELECT ST_Y(location::geometry) as lat FROM addresses WHERE id = ?", [$this->id])->lat ?? null;
    }

    public function getLocationLngAttribute()
    {
        if (array_key_exists('location_lng', $this->attributes)) {
            return $this->attributes['location_lng'];
        }
        return \DB::selectOne("SELECT ST_X(location::geometry) as lng FROM addresses WHERE id = ?", [$this->id])->lng ?? null;
    }
}
