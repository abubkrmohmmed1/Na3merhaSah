<?php

namespace App\Domains\Community\Models;

use App\Domains\Community\Models\UserQuest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    use HasUuids;

    protected $fillable = [
        'title_ar', 'description', 'type', 'goal_type',
        'goal_count', 'xp_reward', 'icon', 'active',
    ];

    public function userQuests(): HasMany
    {
        return $this->hasMany(UserQuest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }
}
