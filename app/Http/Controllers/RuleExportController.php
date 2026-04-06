<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Services\RuleSerializer;

class RuleExportController extends Controller
{
    public function __construct(private RuleSerializer $serializer) {}

    public function index()
    {
        $rules = Rule::where('is_active', true)
            ->whereNotNull('yaml_content')
            ->get()
            ->flatMap(function ($rule) {
                $parsed = $this->serializer->fromYaml($rule->yaml_content);
                return $parsed['rules'] ?? [];
            })
            ->values()
            ->all();

        $yaml = $this->serializer->toYaml(['rules' => $rules]);

        return response($yaml, 200)
            ->header('Content-Type', 'text/yaml')
            ->header('Content-Disposition', 'attachment; filename="rules.yaml"');
    }
}
