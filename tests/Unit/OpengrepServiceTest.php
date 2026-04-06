<?php

namespace Tests\Unit;

use App\Services\OpengrepService;
use PHPUnit\Framework\TestCase;

class OpengrepServiceTest extends TestCase
{
    public function test_format_results_extracts_matches(): void
    {
        $service = new OpengrepService();

        $data = [
            'results' => [[
                'check_id' => 'sqli-concat',
                'start' => ['line' => 3],
                'end' => ['line' => 3],
                'extra' => [
                    'message' => 'SQL injection via concatenation',
                    'severity' => 'WARNING',
                    'lines' => '$q = "SELECT * FROM users WHERE id=" . $id;',
                ],
            ]],
            'errors' => [],
        ];

        $result = $service->formatResults($data);

        $this->assertCount(1, $result['matches']);
        $this->assertEquals('sqli-concat', $result['matches'][0]['rule_id']);
        $this->assertEquals('WARNING', $result['matches'][0]['severity']);
        $this->assertEquals(3, $result['matches'][0]['start']);
        $this->assertEquals(1, $result['total']);
    }

    public function test_format_results_handles_no_matches(): void
    {
        $service = new OpengrepService();

        $result = $service->formatResults(['results' => [], 'errors' => []]);

        $this->assertEmpty($result['matches']);
        $this->assertEquals(0, $result['total']);
    }

    public function test_format_results_passes_through_errors(): void
    {
        $service = new OpengrepService();

        $data = [
            'results' => [],
            'errors' => [['message' => 'invalid pattern', 'type' => ['SemgrepError']]],
        ];

        $result = $service->formatResults($data);

        $this->assertCount(1, $result['errors']);
    }

    public function test_extension_for_maps_languages(): void
    {
        $service = new OpengrepService();

        $this->assertEquals('py', $service->extensionFor('python'));
        $this->assertEquals('js', $service->extensionFor('javascript'));
        $this->assertEquals('php', $service->extensionFor('unknown'));
    }
}
