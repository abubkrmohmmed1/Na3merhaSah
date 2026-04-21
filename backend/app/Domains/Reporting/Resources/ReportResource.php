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
            'started', 'govt_review', 'surveyor_assigned', 'site_visited' => 'red',
            default => 'orange', // For intermediate steps like engineering and execution
        };

        return [
            'id' => $this->id,
            'title' => $this->description ? str()->limit($this->description, 30) : 'بلاغ مدني',
            'digital_address' => $this->address ? $this->address->address_str : 'جاري التحديد...',
            'thumbnail' => $thumbnail,
            'status' => $this->status,
            'status_color' => $statusColor,
            'created_at' => $this->created_at?->diffForHumans(),
        ];
    }
}
