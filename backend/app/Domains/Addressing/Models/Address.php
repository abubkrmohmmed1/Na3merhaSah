<?php

namespace App\Domains\Addressing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Address extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        's2_cell_id',
        'address_str',
        'neighborhood',
        'location'
    ];

    /**
     * Since Laravel 11, basic spatial casts or raw DB inserts are used.
     * We cast location for future usage to ensure it reads correctly.
     */
    protected $casts = [
        // 'location' => PointCast::class // You may uncomment if using a spatial package
    ];
}
