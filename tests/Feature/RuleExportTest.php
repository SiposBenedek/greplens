<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Rule;
use App\Models\RuleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleExportTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $project = new Project([
            'name'       => 'Export Project',
            'slug'       => 'export',
            'is_active'  => true,
            'created_by' => $user->id,
        ]);
        $this->apiKey = $project->setApiKey();
        $project->save();
    }

    public function test_export_returns_yaml_with_active_rules(): void
    {
        $group = RuleGroup::create(['name' => 'Test']);
        Rule::create([
            'rule_group_id' => $group->id,
            'title'         => 'active-rule',
            'yaml_content'  => "rules:\n  - id: active-rule\n    pattern: test\n    message: Active\n    languages: [php]\n    severity: WARNING\n",
            'is_active'     => true,
        ]);

        $response = $this->get('/api/rules', ['X-Api-Key' => $this->apiKey]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/yaml; charset=UTF-8');

        $this->assertStringContains('active-rule', $response->getContent());
    }

    public function test_export_excludes_inactive_rules(): void
    {
        $group = RuleGroup::create(['name' => 'Test']);
        Rule::create([
            'rule_group_id' => $group->id,
            'title'         => 'inactive-rule',
            'yaml_content'  => "rules:\n  - id: inactive-rule\n    pattern: test\n    message: Inactive\n    languages: [php]\n    severity: WARNING\n",
            'is_active'     => false,
        ]);

        $response = $this->get('/api/rules', ['X-Api-Key' => $this->apiKey]);

        $response->assertOk();
        $this->assertStringNotContains('inactive-rule', $response->getContent());
    }

    public function test_export_requires_authentication(): void
    {
        $this->getJson('/api/rules')->assertUnauthorized();
    }

    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(str_contains($haystack, $needle), "Failed asserting that '$haystack' contains '$needle'.");
    }

    private function assertStringNotContains(string $needle, string $haystack): void
    {
        $this->assertFalse(str_contains($haystack, $needle), "Failed asserting that '$haystack' does not contain '$needle'.");
    }
}
