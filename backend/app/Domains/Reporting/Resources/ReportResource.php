<?php

namespace App\Domains\Reporting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Extract the first image to act as a thumbnail if multiple images are uploaded
        $thumbnail = is_array($this->images) && count($this->images) > 0 ? $this->images[0] : null;

        // Front-end requested a color representation for the status
        $statusColor = match($this->status) {
            'resolved' => 'green',
            'external_transfer' => 'gray',
            'started', 'govt_received' => 'blue',
            'surveyor_assigned', 'site_visited', 'admin_approval' => 'orange',
            'engineering_phase', 'bidding_phase', 'execution' => 'red',
            default => 'orange',
        };

        return [
            'id' => $this->id,
            'title' => $this->description ? str()->limit($this->description, 30) : 'بلاغ مدني',
            'digital_address' => $this->address ? $this->address->address_str : 'جاري التحديد...',
            'plus_code' => $this->plus_code,
            'thumbnail' => $thumbnail,
            'status' => $this->status,
            'status_color' => $statusColor,
            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'created_at' => $this->created_at?->diffForHumans(),
            // New Surveyor Fields for the App
            'surveyor_decision' => $this->surveyor_decision,
            'surveyor_notes' => $this->surveyor_notes,
            'surveyor_area' => $this->surveyor_area,
            'surveyor_images' => $this->surveyor_images,
            'first_response_at' => $this->first_response_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
        ];
    }
}
