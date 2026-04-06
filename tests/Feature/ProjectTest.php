<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_lists_projects(): void
    {
        $project = $this->createProject();

        $this->actingAs($this->user)
            ->get('/projects')
            ->assertOk()
            ->assertSee($project->name);
    }

    public function test_create_page_is_accessible(): void
    {
        $this->actingAs($this->user)
            ->get('/projects/create')
            ->assertOk();
    }

    public function test_store_creates_project_and_returns_api_key(): void
    {
        $response = $this->actingAs($this->user)->post('/projects', [
            'name'        => 'My App',
            'url'         => 'https://example.com',
            'description' => 'Test project',
        ]);

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertEquals('My App', $project->name);
        $this->assertEquals('my-app', $project->slug);
        $this->assertEquals($this->user->id, $project->created_by);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('api_key');
    }

    public function test_store_validates_required_name(): void
    {
        $this->actingAs($this->user)
            ->post('/projects', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_show_displays_project(): void
    {
        $project = $this->createProject();

        $this->actingAs($this->user)
            ->get("/projects/{$project->id}")
            ->assertOk()
            ->assertSee($project->name);
    }

    public function test_update_modifies_project(): void
    {
        $project = $this->createProject();

        $this->actingAs($this->user)
            ->patch("/projects/{$project->id}", [
                'name'        => 'Updated Name',
                'description' => 'Updated desc',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertEquals('Updated Name', $project->fresh()->name);
    }

    public function test_regenerate_key_returns_new_key(): void
    {
        $project = $this->createProject();

        $response = $this->actingAs($this->user)
            ->post("/projects/{$project->id}/regenerate-key");

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('api_key');
    }

    public function test_destroy_soft_deletes_project(): void
    {
        $project = $this->createProject();

        $this->actingAs($this->user)
            ->delete("/projects/{$project->id}")
            ->assertRedirect(route('projects.index'));

        $this->assertSoftDeleted($project);
    }

    private function createProject(array $overrides = []): Project
    {
        $project = new Project(array_merge([
            'name'       => 'Test Project',
            'slug'       => 'test-project',
            'is_active'  => true,
            'created_by' => $this->user->id,
        ], $overrides));

        $project->setApiKey();
        $project->save();

        return $project;
    }
}
