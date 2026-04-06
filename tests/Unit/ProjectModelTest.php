<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_api_key_has_correct_prefix(): void
    {
        $key = Project::generateApiKey();

        $this->assertStringStartsWith('glp_', $key);
        $this->assertEquals(44, strlen($key)); // 'glp_' + 40 random chars
    }

    public function test_set_api_key_returns_plain_and_stores_hash(): void
    {
        $project = $this->createProject();
        $plain = $project->setApiKey();
        $project->save();

        $this->assertStringStartsWith('glp_', $plain);
        $this->assertNotEquals($plain, $project->api_key_hash);
        $this->assertTrue($project->verifyApiKey($plain));
    }

    public function test_verify_api_key_rejects_wrong_key(): void
    {
        $project = $this->createProject();
        $project->setApiKey();
        $project->save();

        $this->assertFalse($project->verifyApiKey('glp_wrong_key'));
    }

    public function test_find_by_api_key_returns_project(): void
    {
        $project = $this->createProject();
        $plain = $project->setApiKey();
        $project->save();

        $found = Project::findByApiKey($plain);

        $this->assertNotNull($found);
        $this->assertEquals($project->id, $found->id);
    }

    public function test_find_by_api_key_returns_null_for_invalid(): void
    {
        $this->assertNull(Project::findByApiKey('glp_nonexistent'));
    }

    public function test_find_by_api_key_skips_inactive_projects(): void
    {
        $project = $this->createProject(['is_active' => false]);
        $plain = $project->setApiKey();
        $project->save();

        $this->assertNull(Project::findByApiKey($plain));
    }

    public function test_api_key_hash_is_hidden_from_serialization(): void
    {
        $project = $this->createProject();
        $project->setApiKey();
        $project->save();

        $array = $project->toArray();
        $this->assertArrayNotHasKey('api_key_hash', $array);
    }

    private function createProject(array $overrides = []): Project
    {
        $user = User::factory()->create();

        $project = new Project(array_merge([
            'name'       => 'Test',
            'slug'       => 'test',
            'is_active'  => true,
            'created_by' => $user->id,
        ], $overrides));

        $project->setApiKey();
        $project->save();

        return $project;
    }
}
