<?php

namespace Tests\Feature;

use App\Models\Rule;
use App\Models\RuleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_lists_rules(): void
    {
        $rule = $this->createRule();

        $this->actingAs($this->user)
            ->get('/rules')
            ->assertOk()
            ->assertSee($rule->title);
    }

    public function test_index_filters_by_search(): void
    {
        $this->createRule(['title' => 'sqli-detection']);
        $this->createRule(['title' => 'xss-prevention']);

        $this->actingAs($this->user)
            ->get('/rules?search=sqli')
            ->assertOk()
            ->assertSee('sqli-detection')
            ->assertDontSee('xss-prevention');
    }

    public function test_index_filters_by_active_status(): void
    {
        $this->createRule(['title' => 'active-rule', 'is_active' => true]);
        $this->createRule(['title' => 'inactive-rule', 'is_active' => false]);

        $this->actingAs($this->user)
            ->get('/rules?active=1')
            ->assertOk()
            ->assertSee('active-rule')
            ->assertDontSee('inactive-rule');
    }

    public function test_editor_page_is_accessible(): void
    {
        $rule = $this->createRule();

        $this->actingAs($this->user)
            ->get("/rules/{$rule->id}/editor")
            ->assertOk()
            ->assertSee($rule->title);
    }

    public function test_update_modifies_rule(): void
    {
        $rule = $this->createRule();

        $this->actingAs($this->user)
            ->patch("/rules/{$rule->id}", [
                'title'       => 'Updated Rule',
                'description' => 'Updated description',
            ])
            ->assertRedirect(route('rules.index'));

        $this->assertEquals('Updated Rule', $rule->fresh()->title);
    }

    public function test_update_yaml_saves_content(): void
    {
        $rule = $this->createRule();
        $newYaml = "rules:\n  - id: updated-rule\n    pattern: test\n    message: Updated\n    languages: [php]\n    severity: WARNING\n";

        $this->actingAs($this->user)
            ->patch("/rules/{$rule->id}/yaml", [
                'yaml_content' => $newYaml,
                'test_code'    => '<?php echo "test";',
            ])
            ->assertRedirect(route('rules.editor', $rule));

        $this->assertEquals(trim($newYaml), trim($rule->fresh()->yaml_content));
    }

    public function test_update_yaml_rejects_invalid_yaml(): void
    {
        $rule = $this->createRule();

        $this->actingAs($this->user)
            ->patch("/rules/{$rule->id}/yaml", [
                'yaml_content' => "invalid: yaml: content:\n  - [broken",
            ])
            ->assertSessionHasErrors('yaml_content');
    }

    public function test_import_page_is_accessible(): void
    {
        $this->actingAs($this->user)
            ->get('/rules/import')
            ->assertOk();
    }

    private function createRule(array $overrides = []): Rule
    {
        $group = RuleGroup::create(['name' => 'Test Group']);

        return Rule::create(array_merge([
            'rule_group_id' => $group->id,
            'title'         => 'test-rule',
            'yaml_content'  => "rules:\n  - id: test-rule\n    pattern: test\n    message: Test\n    languages: [php]\n    severity: WARNING\n",
            'is_active'     => true,
        ], $overrides));
    }
}
