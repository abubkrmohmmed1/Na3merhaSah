<?php

namespace App\Domains\Reporting\Actions;

use App\Domains\Reporting\Models\Report;
use App\Domains\Addressing\Actions\ReverseGeocodeAction;
use Illuminate\Support\Facades\Storage;

class ReportIssueAction
{
    public function __construct(
        protected ReverseGeocodeAction $reverseGeocodeAction
    ) {}

    public function execute(array $data, array $imageFiles): Report
    {
        // 1. Spatial Join: Find the neighborhood address ID based on location
        $address = $this->reverseGeocodeAction->execute(
            (float) $data['lat'],
            (float) $data['lng']
        );

        // 2. Handle Image Uploads (Simulated storage for now)
        $imagePaths = [];
        foreach ($imageFiles as $file) {
            $path = $file->store('reports/images', 'public');
            $imagePaths[] = Storage::url($path);
        }

        // 3. Create the Report (linking to the Address)
        return Report::create([
            'user_id' => $data['user_id'] ?? null, // In practice, from auth()
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'location' => "SRID=4326;POINT({$data['lng']} {$data['lat']})",
            's2_cell_id' => $address ? $address->s2_cell_id : 'unmapped',
            'address_id' => $address ? $address->id : null,
            'images' => $imagePaths,
            'status' => 'started',
        ]);
    }
}
