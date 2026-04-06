<?php

namespace App\Services;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use InvalidArgumentException;

class RuleSerializer
{
    /**
     * Default YAML dump settings.
     */
    private int $yamlDepth = 10;
    private int $yamlIndent = 2;

    /**
     * Parse a YAML string into a PHP array.
     *
     * @throws InvalidArgumentException on malformed YAML
     */
    public function fromYaml(string $yaml): array
    {
        if (trim($yaml) === '') {
            throw new InvalidArgumentException('YAML string is empty.');
        }

        try {
            $parsed = Yaml::parse($yaml);
        } catch (ParseException $e) {
            throw new InvalidArgumentException('Failed to parse YAML: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($parsed)) {
            throw new InvalidArgumentException('YAML did not parse into an array.');
        }

        return $parsed;
    }

    /**
     * Serialize a PHP array into a YAML string.
     *
     * @throws InvalidArgumentException on empty data
     */
    public function toYaml(array $data): string
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data array is empty.');
        }

        return Yaml::dump($data, $this->yamlDepth, $this->yamlIndent, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    /**
     * Parse a JSON string into a PHP array.
     *
     * @throws InvalidArgumentException on malformed JSON
     */
    public function fromJson(string $json): array
    {
        if (trim($json) === '') {
            throw new InvalidArgumentException('JSON string is empty.');
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Failed to parse JSON: ' . json_last_error_msg());
        }

        if (!is_array($decoded)) {
            throw new InvalidArgumentException('JSON did not decode into an array.');
        }

        return $decoded;
    }

    /**
     * Serialize a PHP array into a JSON string.
     *
     * @throws InvalidArgumentException on empty data or encode failure
     */
    public function toJson(array $data): string
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data array is empty.');
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new InvalidArgumentException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert a YAML string directly to a JSON string.
     */
    public function yamlToJson(string $yaml): string
    {
        return $this->toJson($this->fromYaml($yaml));
    }

    /**
     * Convert a JSON string directly to a YAML string.
     */
    public function jsonToYaml(string $json): string
    {
        return $this->toYaml($this->fromJson($json));
    }

    /**
     * Validate a YAML string without storing it.
     * Returns true on success, or an error message string on failure.
     */
    public function validateYaml(string $yaml): true|string
    {
        try {
            $this->fromYaml($yaml);
            return true;
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Validate a JSON string without storing it.
     * Returns true on success, or an error message string on failure.
     */
    public function validateJson(string $json): true|string
    {
        try {
            $this->fromJson($json);
            return true;
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Override default YAML dump depth (default: 10).
     */
    public function setYamlDepth(int $depth): static
    {
        $this->yamlDepth = $depth;
        return $this;
    }

    /**
     * Override default YAML indent size (default: 2).
     */
    public function setYamlIndent(int $indent): static
    {
        $this->yamlIndent = $indent;
        return $this;
    }
}