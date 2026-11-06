<?php

namespace App\Domains\Community\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Spatial\Models\GeoFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditSuggestion extends Model
{
    use HasUuids;

    protected $fillable = [
        'geo_feature_id',
        'user_id',
        'field',
        'old_value',
        'new_value',
        'status',
        'confirmations_count',
        'applied_at',
    ];

    public function geoFeature(): BelongsTo
    {
        return $this->belongsTo(GeoFeature::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
