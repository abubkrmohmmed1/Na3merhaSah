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
}
