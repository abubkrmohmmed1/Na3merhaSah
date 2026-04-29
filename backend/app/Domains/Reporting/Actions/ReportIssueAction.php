<?php

namespace App\Domains\Reporting\Actions;

use App\Domains\Reporting\Models\Report;
use App\Domains\Addressing\Actions\ReverseGeocodeAction;
use App\Domains\Spatial\Services\S2GeometryService;
use Illuminate\Support\Facades\Storage;

class ReportIssueAction
{
    public function __construct(
        protected ReverseGeocodeAction $reverseGeocodeAction,
        protected S2GeometryService $s2Service
    ) {}

    public function execute(array $data, array $imageFiles): Report
    {
        // 1. Validate workflow step
        $workflowStep = $data['workflow_step'] ?? 'location_selection';
        $this->validateWorkflowStep($workflowStep, $data);

        // 2. Spatial Join: Find the neighborhood address ID based on location
        $address = $this->reverseGeocodeAction->execute(
            (float) $data['lat'],
            (float) $data['lng']
        );

        // Generate high-precision Plus Code for this specific point
        $plusCode = $this->s2Service->generatePlusCode((float) $data['lat'], (float) $data['lng']);

        // 3. Handle Image Uploads (Simulated storage for now)
        $imagePaths = [];
        foreach ($imageFiles as $file) {
            $path = $file->store('reports/images', 'public');
            $imagePaths[] = Storage::url($path);
        }

        // 4. Prepare workflow metadata
        $workflowMetadata = [
            'current_step' => $workflowStep,
            'completed_steps' => $this->getCompletedSteps($workflowStep),
            'validation_status' => $this->getValidationStatus($data),
            'step_timestamps' => $data['step_timestamps'] ?? [],
        ];

        // 5. Create the Report (linking to the Address)
        return Report::create([
            'user_id' => $data['user_id'] ?? null, // In practice, from auth()
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'location' => \DB::raw("ST_SetSRID(ST_MakePoint(" . (float) $data['lng'] . ", " . (float) $data['lat'] . "), 4326)"),
            's2_cell_id' => $address ? $address->s2_cell_id : $this->s2Service->latLngToToken((float) $data['lat'], (float) $data['lng']),
            'plus_code' => $plusCode,
            'address_id' => $address ? $address->id : null,
            'images' => $imagePaths,
            'status' => 'started',
            'workflow_step' => $workflowStep,
            'workflow_metadata' => $workflowMetadata,
        ]);
    }

    private function validateWorkflowStep(string $step, array $data): void
    {
        switch ($step) {
            case 'location_selection':
                if (!isset($data['lat']) || !isset($data['lng'])) {
                    throw new \InvalidArgumentException('Location coordinates are required');
                }
                break;
            case 'category_selection':
                if (!isset($data['category_id'])) {
                    throw new \InvalidArgumentException('Category selection is required');
                }
                break;
            case 'description_input':
                if (empty($data['description'])) {
                    throw new \InvalidArgumentException('Description is required');
                }
                break;
            case 'image_upload':
                if (empty($data['has_images'])) {
                    throw new \InvalidArgumentException('At least one image is required');
                }
                break;
        }
    }

    private function getCompletedSteps(string $currentStep): array
    {
        $stepOrder = ['location_selection', 'category_selection', 'description_input', 'image_upload', 'review_submit'];
        $currentIndex = array_search($currentStep, $stepOrder);
        return array_slice($stepOrder, 0, $currentIndex + 1);
    }

    private function getValidationStatus(array $data): array
    {
        return [
            'location_valid' => isset($data['lat']) && isset($data['lng']),
            'category_valid' => isset($data['category_id']),
            'description_valid' => !empty($data['description']),
            'images_valid' => !empty($data['has_images']),
        ];
    }
}
