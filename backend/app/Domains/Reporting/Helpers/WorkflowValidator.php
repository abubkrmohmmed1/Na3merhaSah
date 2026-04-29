<?php

namespace App\Domains\Reporting\Helpers;

class WorkflowValidator
{
    public static function validateStep(string $step, array $data): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
        ];

        switch ($step) {
            case 'location_selection':
                $validation = self::validateLocationStep($data);
                break;
            case 'category_selection':
                $validation = self::validateCategoryStep($data);
                break;
            case 'description_input':
                $validation = self::validateDescriptionStep($data);
                break;
            case 'image_upload':
                $validation = self::validateImageStep($data);
                break;
            case 'review_submit':
                $validation = self::validateReviewStep($data);
                break;
        }

        return $validation;
    }

    private static function validateLocationStep(array $data): array
    {
        $errors = [];
        $warnings = [];

        if (!isset($data['lat']) || !isset($data['lng'])) {
            $errors[] = 'Location coordinates are required';
        }

        if (isset($data['lat']) && ($data['lat'] < -90 || $data['lat'] > 90)) {
            $errors[] = 'Invalid latitude value';
        }

        if (isset($data['lng']) && ($data['lng'] < -180 || $data['lng'] > 180)) {
            $errors[] = 'Invalid longitude value';
        }

        if (isset($data['s2_cell_id']) && empty($data['s2_cell_id'])) {
            $warnings[] = 'Location may not be precisely mapped';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private static function validateCategoryStep(array $data): array
    {
        $errors = [];
        $warnings = [];

        if (!isset($data['category_id'])) {
            $errors[] = 'Category selection is required';
        }

        $validCategories = [1, 2, 3, 4, 5, 6]; // Based on frontend categories
        if (isset($data['category_id']) && !in_array($data['category_id'], $validCategories)) {
            $errors[] = 'Invalid category selection';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private static function validateDescriptionStep(array $data): array
    {
        $errors = [];
        $warnings = [];

        if (!isset($data['description']) || empty(trim($data['description']))) {
            $errors[] = 'Description is required';
        }

        if (isset($data['description']) && strlen($data['description']) < 10) {
            $warnings[] = 'Description should be more detailed';
        }

        if (isset($data['description']) && strlen($data['description']) > 1000) {
            $warnings[] = 'Description is quite long';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private static function validateImageStep(array $data): array
    {
        $errors = [];
        $warnings = [];

        if (!isset($data['has_images']) || $data['has_images'] === false) {
            $warnings[] = 'No images attached - consider adding photos for better context';
        }

        if (isset($data['images_count']) && $data['images_count'] > 5) {
            $warnings[] = 'Many images attached - consider selecting the most relevant ones';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private static function validateReviewStep(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Check all previous steps are valid
        $previousSteps = ['location_selection', 'category_selection', 'description_input'];
        
        foreach ($previousSteps as $step) {
            $stepValidation = self::validateStep($step, $data);
            if (!$stepValidation['valid']) {
                $errors[] = "Previous step '{$step}' has validation errors";
            }
        }

        // Check workflow completion
        if (isset($data['completed_steps']) && count($data['completed_steps']) < 3) {
            $warnings[] = 'Some workflow steps may be incomplete';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    public static function getWorkflowProgress(array $workflowMetadata): array
    {
        $totalSteps = 5; // Total workflow steps
        $completedSteps = $workflowMetadata['completed_steps'] ?? [];
        $currentStep = $workflowMetadata['current_step'] ?? 'location_selection';
        
        $progress = [
            'total_steps' => $totalSteps,
            'completed_steps' => count($completedSteps),
            'current_step' => $currentStep,
            'progress_percentage' => (count($completedSteps) / $totalSteps) * 100,
            'next_step' => self::getNextStep($currentStep),
            'is_complete' => in_array('review_submit', $completedSteps),
        ];

        return $progress;
    }

    private static function getNextStep(string $currentStep): string
    {
        $stepOrder = ['location_selection', 'category_selection', 'description_input', 'image_upload', 'review_submit'];
        $currentIndex = array_search($currentStep, $stepOrder);
        
        if ($currentIndex !== false && $currentIndex < count($stepOrder) - 1) {
            return $stepOrder[$currentIndex + 1];
        }
        
        return 'review_submit'; // Default to final step
    }
}
