<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Rule;
use App\Models\RuleGroup;
use App\Services\OpengrepService;
use App\Services\RuleSerializer;

class RuleController extends Controller
{
    public function __construct(private RuleSerializer $serializer) {}

    public function index(Request $request)
    {
        $query = Rule::with('group');

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->input('active') !== null && $request->input('active') !== '') {
            $query->where('is_active', $request->boolean('active'));
        }

        $rules = $query->orderBy('title')->get()->map(function ($rule) {
            $meta = $this->extractMeta($rule->yaml_content);
            $rule->severity  = $meta['severity'];
            $rule->languages = $meta['languages'];
            return $rule;
        });

        $language = $request->input('language');
        $languages = $rules->pluck('languages')->flatten()->unique()->sort()->values();

        if ($language) {
            $rules = $rules->filter(fn($r) => in_array($language, $r->languages));
        }

        $page = $request->integer('page', 1);
        $perPage = 10;
        $rules = new LengthAwarePaginator(
            $rules->forPage($page, $perPage)->values(),
            $rules->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('rules.index', compact('rules', 'languages'));
    }

    private function extractMeta(?string $yamlContent): array
    {
        $default = ['severity' => null, 'languages' => []];

        if (!$yamlContent) {
            return $default;
        }

        try {
            $parsed = $this->serializer->fromYaml($yamlContent);
        } catch (\InvalidArgumentException) {
            return $default;
        }

        $first = $parsed['rules'][0] ?? [];

        return [
            'severity'  => $first['severity'] ?? $first['metadata']['severity'] ?? null,
            'languages' => $first['languages'] ?? [],
        ];
    }

    public function create()
    {
        $groups = RuleGroup::orderBy('name')->get();

        return view('rules.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'rule_group_id' => 'required_without:new_group|nullable|exists:rule_groups,id',
            'new_group'     => 'required_without:rule_group_id|nullable|string|max:255',
        ]);

        $groupId = $validated['rule_group_id'];

        if (!empty($validated['new_group'])) {
            $group = RuleGroup::firstOrCreate(['name' => $validated['new_group']]);
            $groupId = $group->id;
        }

        $title = $validated['title'];

        $rule = Rule::create([
            'title'         => $title,
            'description'   => $validated['description'] ?? '',
            'rule_group_id' => $groupId,
            'is_active'     => true,
            'yaml_content'  => "rules:\n  - id: {$title}\n    pattern: \"TODO\"\n    message: \"TODO: describe what this rule detects\"\n    languages: [TODO: add language]\n    severity: WARNING\n",
        ]);

        return redirect()->route('rules.editor', $rule)
            ->with('success', 'Rule created. Edit the YAML to define your pattern.');
    }

    public function update(Request $request, Rule $rule)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'is_active'   => 'boolean',
        ]);

        $rule->update($validated);

        return redirect()->route('rules.index')->with('success', 'Rule updated successfully.');
    }

    public function editor(Rule $rule)
    {
        return view('rules.editor', compact('rule'));
    }

    public function updateYaml(Request $request, Rule $rule)
    {
        $request->validate([
            'yaml_content' => 'required|string|max:50000',
            'test_code'    => 'nullable|string|max:10000',
        ]);

        $result = $this->serializer->validateYaml($request->yaml_content);
        if ($result !== true) {
            return back()->withErrors(['yaml_content' => 'Invalid YAML: ' . $result]);
        }

        $rule->update([
            'yaml_content' => $request->yaml_content,
            'test_code'    => $request->test_code,
        ]);

        return redirect()->route('rules.editor', $rule)->with('success', 'YAML updated successfully.');
    }

    public function runTest(Request $request, Rule $rule, OpengrepService $opengrep)
    {
        $request->validate([
            'test_code'    => 'required|string|max:10000',
            'yaml_content' => 'nullable|string|max:50000',
            'language'     => 'nullable|string|in:php,python,javascript,typescript,java,go,ruby',
        ]);

        if (!$opengrep->isEnabled()) {
            return response()->json(['error' => 'Opengrep is not enabled on this instance.'], 503);
        }

        $yaml = $request->yaml_content ?: $rule->yaml_content;
        $test = $request->test_code ?? $rule->test_code;

        $results = $opengrep->run($yaml, $test, $request->input('language'));

        return response()->json($results);
    }

    public function import()
    {
        return view('rules.import');
    }
}
