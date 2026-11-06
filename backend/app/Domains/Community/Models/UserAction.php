<?php

namespace App\Domains\Community\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Spatial\Models\GeoFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAction extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'geo_feature_id',
        'action_type',
        'payload',
        'points',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function geoFeature(): BelongsTo
    {
        return $this->belongsTo(GeoFeature::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('action_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public static function pointsForAction(string $type): int
    {
        return match ($type) {
            'add' => 50,
            'confirm' => 10,
            'vote' => 5,
            'flag' => 15,
            'confirm_suggestion' => 10,
            'login' => 10,
            'quest_reward' => 0, // XP comes from quest definition
            default => 0,
        };
    }
}
