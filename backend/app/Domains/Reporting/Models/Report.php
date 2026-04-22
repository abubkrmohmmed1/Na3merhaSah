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
        'category_id',
        'status',
        'address_id',
        'description',
        'images',
        'workflow_step',
        'workflow_metadata'
    ];

    protected $casts = [
        'images' => 'array',
        'workflow_metadata' => 'array',
        'category_id' => 'integer',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
