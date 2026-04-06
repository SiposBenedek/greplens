<?php

namespace Tests\Feature;

use App\Models\Finding;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->project = new Project([
            'name'       => 'Test Project',
            'slug'       => 'test-project',
            'is_active'  => true,
            'created_by' => $this->user->id,
        ]);
        $this->project->setApiKey();
        $this->project->save();
    }

    public function test_index_shows_projects_with_findings(): void
    {
        $this->createFinding();

        $this->actingAs($this->user)
            ->get('/findings')
            ->assertOk()
            ->assertSee('Test Project');
    }

    public function test_index_hides_projects_without_findings(): void
    {
        $this->actingAs($this->user)
            ->get('/findings')
            ->assertOk()
            ->assertDontSee('Test Project');
    }

    public function test_show_displays_findings_for_project(): void
    {
        $finding = $this->createFinding();

        $this->actingAs($this->user)
            ->get("/findings/{$this->project->id}")
            ->assertOk()
            ->assertSee($finding->check_id)
            ->assertSee($finding->message);
    }

    public function test_show_filters_by_severity(): void
    {
        $this->createFinding(['severity' => 'ERROR', 'check_id' => 'error-rule']);
        $this->createFinding(['severity' => 'INFO', 'check_id' => 'info-rule']);

        $this->actingAs($this->user)
            ->get("/findings/{$this->project->id}?severity=ERROR")
            ->assertOk()
            ->assertSee('error-rule')
            ->assertDontSee('info-rule');
    }

    public function test_show_filters_by_search(): void
    {
        $this->createFinding(['check_id' => 'sqli-rule', 'message' => 'SQL injection']);
        $this->createFinding(['check_id' => 'xss-rule', 'message' => 'Cross-site scripting']);

        $this->actingAs($this->user)
            ->get("/findings/{$this->project->id}?search=sqli")
            ->assertOk()
            ->assertSee('sqli-rule')
            ->assertDontSee('xss-rule');
    }

    public function test_show_filters_by_status(): void
    {
        $this->createFinding(['check_id' => 'flagged-rule', 'status' => 'flagged']);
        $this->createFinding(['check_id' => 'open-rule', 'status' => 'unreviewed']);

        $this->actingAs($this->user)
            ->get("/findings/{$this->project->id}?status=flagged")
            ->assertOk()
            ->assertSee('flagged-rule')
            ->assertDontSee('open-rule');
    }

    public function test_update_status_flags_finding(): void
    {
        $finding = $this->createFinding();

        $this->actingAs($this->user)
            ->patch("/findings/{$finding->id}/status", ['status' => 'flagged'])
            ->assertRedirect();

        $this->assertEquals('flagged', $finding->fresh()->status);
    }

    public function test_update_status_suppresses_finding(): void
    {
        $finding = $this->createFinding();

        $this->actingAs($this->user)
            ->patch("/findings/{$finding->id}/status", ['status' => 'suppressed'])
            ->assertRedirect();

        $this->assertEquals('suppressed', $finding->fresh()->status);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $finding = $this->createFinding();

        $this->actingAs($this->user)
            ->patch("/findings/{$finding->id}/status", ['status' => 'invalid'])
            ->assertSessionHasErrors('status');
    }

    public function test_show_scopes_to_latest_scan_by_default(): void
    {
        $this->createFinding(['check_id' => 'old-rule', 'scanned_at' => now()->subDay()]);
        $this->createFinding(['check_id' => 'new-rule', 'scanned_at' => now()]);

        $this->actingAs($this->user)
            ->get("/findings/{$this->project->id}")
            ->assertOk()
            ->assertSee('new-rule')
            ->assertDontSee('old-rule');
    }

    private function createFinding(array $overrides = []): Finding
    {
        return Finding::create(array_merge([
            'project_id' => $this->project->id,
            'check_id'   => 'test-rule',
            'file_path'  => 'app/Test.php',
            'start_line' => 10,
            'start_col'  => 1,
            'end_line'   => 10,
            'end_col'    => 30,
            'message'    => 'Test finding',
            'severity'   => 'WARNING',
            'status'     => 'unreviewed',
            'scanned_at' => now(),
        ], $overrides));
    }
}
