<?php

namespace App\Services;

use App\Models\Rule;
use App\Models\RuleGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RuleImportService
{
    public function __construct(
        private ZipExtractorService $extractor,
        private RuleSerializer $serializer,
    ) {}

    /**
     * Extract zip, import all rules and groups, clean up.
     * Returns stats: ['groups' => int, 'rules' => int]
     *
     * @throws \RuntimeException
     */
    public function importFromZip(string $zipPath): array
    {
        $extractPath = $this->extractor->extract($zipPath);

        try {
            return DB::transaction(function () use ($extractPath) {
                return $this->processExtractedFiles($extractPath);
            });
        } finally {
            $this->extractor->cleanup($extractPath);
        }
    }

    private function processExtractedFiles(string $basePath): array
    {
        $stats = ['groups' => 0, 'rules' => 0];

        $basePath = $this->resolveBasePath($basePath);

        $this->processDirectory($basePath, null, $stats);

        if ($stats['groups'] === 0 && $stats['rules'] === 0) {
            throw new \RuntimeException('No groups or rules found in zip.');
        }

        return $stats;
    }

    private function processDirectory(string $path, ?int $parentId, array &$stats): void
    {
        if ($parentId !== null) {
            $yamlFiles = collect(File::glob("{$path}/*.yaml"))
                ->merge(File::glob("{$path}/*.yml"))
                ->reject(fn($f) => str_contains(basename($f), '.test.'))
                ->toArray();

            foreach ($yamlFiles as $yamlFile) {
                $stats['rules'] += $this->upsertRulesFromFile($parentId, $yamlFile);
            }
        }

        foreach (File::directories($path) as $dir) {
            $groupName = basename($dir);

            $group = RuleGroup::withTrashed()
                ->where('name', $groupName)
                ->where('parent_id', $parentId)
                ->first();

            if ($group) {
                $group->restore();
                $group->update(['parent_id' => $parentId]);
            } else {
                $group = RuleGroup::create([
                    'name'      => $groupName,
                    'parent_id' => $parentId,
                ]);
            }

            $stats['groups']++;

            $this->processDirectory($dir, $group->id, $stats);
        }
    }

    private function upsertRulesFromFile(int $groupId, string $yamlFile): int
    {
        $rawContent = File::get($yamlFile);

        try {
            $parsed = $this->serializer->fromYaml($rawContent);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException("Invalid YAML in {$yamlFile}: " . $e->getMessage());
        }

        if (empty($parsed['rules']) || !is_array($parsed['rules'])) {
            return 0;
        }

        $count = 0;

        foreach ($parsed['rules'] as $ruleData) {
            if (empty($ruleData['id'])) {
                continue;
            }

            $yaml = $this->serializer->toYaml(['rules' => [$ruleData]]);

            $existing = Rule::withTrashed()
                ->where('rule_group_id', $groupId)
                ->where('title', $ruleData['id'])
                ->first();

            if ($existing) {
                $existing->restore();
                $existing->update([
                    'yaml_content' => $yaml,
                    'is_active'    => true,
                ]);
            } else {
                Rule::create([
                    'rule_group_id' => $groupId,
                    'title'         => $ruleData['id'],
                    'yaml_content'  => $yaml,
                    'is_active'     => true,
                ]);
            }

            $count++;
        }

        return $count;
    }

    /**
     * Unwrap single top-level directory zips and strip __MACOSX artifacts.
     */
    private function resolveBasePath(string $basePath): string
    {
        $macOsx = "{$basePath}/__MACOSX";

        if (File::isDirectory($macOsx)) {
            File::deleteDirectory($macOsx);
        }

        $dirs      = File::directories($basePath);
        $rootYamls = array_merge(
            File::glob("{$basePath}/*.yaml"),
            File::glob("{$basePath}/*.yml"),
        );

        if (count($dirs) === 1 && empty($rootYamls)) {
            return $dirs[0];
        }

        return $basePath;
    }
}