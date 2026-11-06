<?php

namespace App\Domains\Community\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Community\Models\UserAchievement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    use HasUuids;

    protected $fillable = [
        'title_ar', 'description', 'icon', 'condition_type',
        'condition_value', 'xp_reward', 'active',
    ];

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }
}
