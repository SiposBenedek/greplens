<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindingApiTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;
    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->project = new Project([
            'name'       => 'API Test Project',
            'slug'       => 'api-test',
            'is_active'  => true,
            'created_by' => $user->id,
        ]);
        $this->apiKey = $this->project->setApiKey();
        $this->project->save();
    }

    public function test_store_findings_with_valid_api_key(): void
    {
        $response = $this->postJson('/api/findings', [
            'results' => [$this->makeFindingPayload()],
        ], ['X-Api-Key' => $this->apiKey]);

        $response->assertOk()
            ->assertJson(['message' => 'Findings synced.', 'count' => 1]);

        $this->assertDatabaseCount('findings', 1);
        $this->assertDatabaseHas('findings', [
            'project_id' => $this->project->id,
            'check_id'   => 'test-rule',
            'file_path'  => 'app/Test.php',
            'severity'   => 'WARNING',
        ]);
    }

    public function test_store_rejects_without_api_key(): void
    {
        $this->postJson('/api/findings', [
            'results' => [$this->makeFindingPayload()],
        ])->assertUnauthorized();
    }

    public function test_store_rejects_invalid_api_key(): void
    {
        $this->postJson('/api/findings', [
            'results' => [$this->makeFindingPayload()],
        ], ['X-Api-Key' => 'glp_invalid'])->assertUnauthorized();
    }

    public function test_store_validates_results_structure(): void
    {
        $this->postJson('/api/findings', [
            'results' => [['bad' => 'data']],
        ], ['X-Api-Key' => $this->apiKey])
            ->assertUnprocessable();
    }

    public function test_store_requires_results(): void
    {
        $this->postJson('/api/findings', [], ['X-Api-Key' => $this->apiKey])
            ->assertUnprocessable();
    }

    public function test_multiple_syncs_create_separate_scans(): void
    {
        $this->postJson('/api/findings', [
            'results' => [$this->makeFindingPayload()],
        ], ['X-Api-Key' => $this->apiKey])->assertOk();

        // Small delay to ensure different scanned_at
        $this->travel(1)->minutes();

        $this->postJson('/api/findings', [
            'results' => [$this->makeFindingPayload(), $this->makeFindingPayload('rule-2')],
        ], ['X-Api-Key' => $this->apiKey])->assertOk();

        $this->assertDatabaseCount('findings', 3);

        $scans = $this->project->findings()
            ->distinct('scanned_at')
            ->pluck('scanned_at');
        $this->assertCount(2, $scans);
    }

    public function test_store_updates_project_last_seen_at(): void
    {
        $this->assertNull($this->project->fresh()->last_seen_at);

        $this->postJson('/api/findings', [
            'results' => [$this->makeFindingPayload()],
        ], ['X-Api-Key' => $this->apiKey])->assertOk();

        $this->assertNotNull($this->project->fresh()->last_seen_at);
    }

    public function test_inactive_project_key_is_rejected(): void
    {
        $this->project->update(['is_active' => false]);

        $this->postJson('/api/findings', [
            'results' => [$this->makeFindingPayload()],
        ], ['X-Api-Key' => $this->apiKey])->assertUnauthorized();
    }

    private function makeFindingPayload(string $checkId = 'test-rule'): array
    {
        return [
            'check_id' => $checkId,
            'path'     => 'app/Test.php',
            'start'    => ['line' => 10, 'col' => 5],
            'end'      => ['line' => 10, 'col' => 40],
            'extra'    => [
                'message'  => 'Test finding message',
                'severity' => 'WARNING',
                'lines'    => '$x = "test";',
            ],
        ];
    }
}
