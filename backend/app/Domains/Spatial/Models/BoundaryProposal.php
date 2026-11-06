<?php

namespace App\Domains\Spatial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoundaryProposal extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'proposal_type', 'name', 'lat', 'lng', 'geom',
        's2_cell_id', 'plus_code', 'state_id', 'state_name',
        'locality_id', 'locality_name', 'description', 'phone',
        'status', 'reviewed_by', 'reviewed_at', 'rejection_reason', 'metadata',
    ];

    protected $casts = [
        'lat' => 'double',
        'lng' => 'double',
        'metadata' => 'array',
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
    public function scopeNeighborhoods($q) { return $q->where('proposal_type', 'neighborhood'); }
    public function scopeVillages($q) { return $q->where('proposal_type', 'village'); }

    public function approve(array $metadata = []): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], $metadata),
        ]);
    }

    public function reject(string $reason): void
    {
        $this->update(['status' => 'rejected', 'rejection_reason' => $reason, 'reviewed_at' => now()]);
    }
}