<?php

namespace App\Domains\Community\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Community\Models\Quest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuest extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'quest_id', 'progress', 'completed_at', 'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'claimed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
}
