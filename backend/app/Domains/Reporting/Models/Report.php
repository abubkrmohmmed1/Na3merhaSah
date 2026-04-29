<?php

namespace App\Domains\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Addressing\Models\Address;

class Report extends Model
{
    use HasUuids, SoftDeletes;

    // حالات سير العمل (Workflow States)
    const STATUS_STARTED = 'started';
    const STATUS_GOVT_RECEIVED = 'govt_received';
    const STATUS_EXTERNAL_TRANSFER = 'external_transfer';
    const STATUS_SURVEYOR_ASSIGNED = 'surveyor_assigned';
    const STATUS_SITE_VISITED = 'site_visited';
    const STATUS_ENGINEERING_PHASE = 'engineering_phase';
    const STATUS_BIDDING_PHASE = 'bidding_phase';
    const STATUS_EXECUTION = 'execution';
    const STATUS_ADMIN_APPROVAL = 'admin_approval';
    const STATUS_RESOLVED = 'resolved';

    // خطوات سير العمل (Workflow Steps)
    const STEP_LOCATION = 'location_selection';
    const STEP_CATEGORY = 'category_selection';
    const STEP_DESCRIPTION = 'description_input';
    const STEP_IMAGE = 'image_upload';
    const STEP_REVIEW = 'review_submit';

    protected $fillable = [
        'id',
        'user_id',
        'location',
        's2_cell_id',
        'plus_code',
        'category_id',
        'status',
        'address_id',
        'description',
        'images',
        'workflow_step',
        'workflow_metadata',
        'surveyor_decision',
        'surveyor_notes',
        'surveyor_area',
        'surveyor_images',
        'first_response_at',
        'resolved_at',
        'user_feedback',
        'user_rating',
        'feedback_quality',
        'feedback_time',
        'feedback_behavior',
        'feedback_cleanliness',
        'feedback_main_issue',
        'feedback_images',
    ];

    protected $casts = [
        'images' => 'array',
        'surveyor_images' => 'array',
        'feedback_images' => 'array',
        'workflow_metadata' => 'array',
        'category_id' => 'integer',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * PERF-01: Scope to load coordinates in one query instead of N+1.
     * Usage: Report::withCoordinates()->get()
     */
    public function scopeWithCoordinates($query)
    {
        return $query->selectRaw('*, ST_Y(location::geometry) as location_lat, ST_X(location::geometry) as location_lng');
    }

    public function getLocationLatAttribute()
    {
        // Use pre-loaded value from withCoordinates() scope, fallback to DB query
        if (array_key_exists('location_lat', $this->attributes)) {
            return $this->attributes['location_lat'];
        }
        return \DB::selectOne("SELECT ST_Y(location::geometry) as lat FROM reports WHERE id = ?", [$this->id])->lat ?? null;
    }

    public function getLocationLngAttribute()
    {
        // Use pre-loaded value from withCoordinates() scope, fallback to DB query
        if (array_key_exists('location_lng', $this->attributes)) {
            return $this->attributes['location_lng'];
        }
        return \DB::selectOne("SELECT ST_X(location::geometry) as lng FROM reports WHERE id = ?", [$this->id])->lng ?? null;
    }
}
