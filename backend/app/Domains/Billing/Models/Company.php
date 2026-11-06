<?php

namespace App\Domains\Billing\Models;

use App\Domains\Billing\Models\ApiKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'webhook_url',
        'sudapass_id',
        'plan',
        'status',
    ];

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
