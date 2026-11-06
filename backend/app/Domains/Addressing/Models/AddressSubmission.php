<?php

namespace App\Domains\Addressing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'name', 'address_str', 'neighborhood',
        'type', 'service_layer', 'lat', 'lng', 'location',
        's2_cell_id', 'plus_code', 'phone', 'notes',
        'status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'lat' => 'double',
        'lng' => 'double',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Auth\Models\User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Auth\Models\User::class, 'reviewed_by');
    }

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopeRejected($q) { return $q->where('status', 'rejected'); }
    public function scopePersonal($q) { return $q->where('type', 'personal'); }
    public function scopeService($q) { return $q->where('type', 'service'); }

    public function approve(): void
    {
        $this->update(['status' => 'approved', 'reviewed_at' => now()]);
    }

    public function reject(string $reason): void
    {
        $this->update(['status' => 'rejected', 'rejection_reason' => $reason, 'reviewed_at' => now()]);
    }
}