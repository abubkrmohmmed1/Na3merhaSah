<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domains\Reporting\Actions\ReportIssueAction;
use App\Domains\Addressing\Actions\ReverseGeocodeAction;
use App\Domains\Spatial\Services\S2GeometryService;
use App\Domains\Reporting\Helpers\WorkflowValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkflowTest extends TestCase
{
    private ReportIssueAction $reportIssueAction;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->reportIssueAction = new ReportIssueAction(
            new ReverseGeocodeAction(new S2GeometryService())
        );
    }

    public function test_workflow_validation_location_step()
    {
        $validData = ['lat' => 24.7136, 'lng' => 46.6753];
        $validation = WorkflowValidator::validateStep('location_selection', $validData);
        
        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    public function test_workflow_validation_category_step()
    {
        $validData = ['category_id' => 1];
        $validation = WorkflowValidator::validateStep('category_selection', $validData);
        
        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    public function test_workflow_validation_description_step()
    {
        $validData = ['description' => 'There is a water leak in the main street'];
        $validation = WorkflowValidator::validateStep('description_input', $validData);
        
        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    public function test_workflow_progress_calculation()
    {
        $workflowMetadata = [
            'current_step' => 'description_input',
            'completed_steps' => ['location_selection', 'category_selection', 'description_input']
        ];

        $progress = WorkflowValidator::getWorkflowProgress($workflowMetadata);

        $this->assertEquals(5, $progress['total_steps']);
        $this->assertEquals(3, $progress['completed_steps']);
        $this->assertEquals(60.0, $progress['progress_percentage']);
        $this->assertEquals('image_upload', $progress['next_step']);
        $this->assertFalse($progress['is_complete']);
    }

    public function test_report_creation_with_workflow_metadata()
    {
        Storage::fake('public');

        $reportData = [
            'lat' => 24.7136,
            'lng' => 46.6753,
            'description' => 'Water leak reported',
            'category_id' => 1,
            'workflow_step' => 'review_submit',
            'step_timestamps' => [
                'location_selection' => '2024-01-01T10:00:00Z',
                'category_selection' => '2024-01-01T10:01:00Z',
            ],
        ];

        $imageFiles = [
            UploadedFile::fake()->image('report1.jpg'),
        ];

        $report = $this->reportIssueAction->execute($reportData, $imageFiles);

        $this->assertNotNull($report);
        $this->assertEquals('review_submit', $report->workflow_step);
        $this->assertIsArray($report->workflow_metadata);
        $this->assertArrayHasKey('current_step', $report->workflow_metadata);
        $this->assertArrayHasKey('completed_steps', $report->workflow_metadata);
        $this->assertArrayHasKey('validation_status', $report->workflow_metadata);
    }

    public function test_workflow_step_validation_errors()
    {
        $invalidData = []; // No coordinates
        $validation = WorkflowValidator::validateStep('location_selection', $invalidData);
        
        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
        $this->assertStringContains('coordinates', $validation['errors'][0]);
    }
}
