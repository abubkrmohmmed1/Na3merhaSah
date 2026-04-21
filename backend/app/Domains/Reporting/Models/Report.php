<?php

namespace App\Domains\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Addressing\Models\Address;

class Report extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'location',
        's2_cell_id',
        'category_id',
        'status',
        'address_id',
        'description',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
