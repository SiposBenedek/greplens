<?php

namespace Tests\Unit;

use App\Services\RuleSerializer;
use PHPUnit\Framework\TestCase;

class RuleSerializerTest extends TestCase
{
    private RuleSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new RuleSerializer();
    }

    public function test_from_yaml_parses_valid_yaml(): void
    {
        $yaml = "rules:\n  - id: test\n    message: hello\n";
        $result = $this->serializer->fromYaml($yaml);

        $this->assertArrayHasKey('rules', $result);
        $this->assertEquals('test', $result['rules'][0]['id']);
    }

    public function test_from_yaml_throws_on_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serializer->fromYaml('');
    }

    public function test_from_yaml_throws_on_invalid_yaml(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serializer->fromYaml("invalid: yaml: \n  - [broken");
    }

    public function test_from_yaml_throws_on_scalar(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serializer->fromYaml('just a string');
    }

    public function test_to_yaml_serializes_array(): void
    {
        $data = ['rules' => [['id' => 'test']]];
        $yaml = $this->serializer->toYaml($data);

        $this->assertStringContainsString('rules:', $yaml);
        $this->assertStringContainsString('test', $yaml);
    }

    public function test_to_yaml_throws_on_empty_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serializer->toYaml([]);
    }

    public function test_from_json_parses_valid_json(): void
    {
        $json = '{"rules": [{"id": "test"}]}';
        $result = $this->serializer->fromJson($json);

        $this->assertEquals('test', $result['rules'][0]['id']);
    }

    public function test_from_json_throws_on_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serializer->fromJson('');
    }

    public function test_from_json_throws_on_invalid_json(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serializer->fromJson('{broken');
    }

    public function test_to_json_serializes_array(): void
    {
        $data = ['rules' => [['id' => 'test']]];
        $json = $this->serializer->toJson($data);

        $decoded = json_decode($json, true);
        $this->assertEquals('test', $decoded['rules'][0]['id']);
    }

    public function test_to_json_throws_on_empty_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serializer->toJson([]);
    }

    public function test_yaml_to_json_converts(): void
    {
        $yaml = "rules:\n  - id: test\n";
        $json = $this->serializer->yamlToJson($yaml);
        $decoded = json_decode($json, true);

        $this->assertEquals('test', $decoded['rules'][0]['id']);
    }

    public function test_json_to_yaml_converts(): void
    {
        $json = '{"rules": [{"id": "test"}]}';
        $yaml = $this->serializer->jsonToYaml($json);

        $this->assertStringContainsString('rules:', $yaml);
    }

    public function test_validate_yaml_returns_true_for_valid(): void
    {
        $this->assertTrue($this->serializer->validateYaml("key: value\n"));
    }

    public function test_validate_yaml_returns_error_for_invalid(): void
    {
        $result = $this->serializer->validateYaml('');
        $this->assertIsString($result);
    }

    public function test_validate_json_returns_true_for_valid(): void
    {
        $this->assertTrue($this->serializer->validateJson('{"key": "value"}'));
    }

    public function test_validate_json_returns_error_for_invalid(): void
    {
        $result = $this->serializer->validateJson('');
        $this->assertIsString($result);
    }

    public function test_set_yaml_depth_is_fluent(): void
    {
        $result = $this->serializer->setYamlDepth(5);
        $this->assertInstanceOf(RuleSerializer::class, $result);
    }

    public function test_set_yaml_indent_is_fluent(): void
    {
        $result = $this->serializer->setYamlIndent(4);
        $this->assertInstanceOf(RuleSerializer::class, $result);
    }
}
