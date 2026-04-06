<?php

namespace App\Services;

use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class OpengrepService
{
    public function isEnabled(): bool
    {
        return config('opengrep.enabled') && $this->binaryExists();
    }

    public function run(string $yamlContent, string $testCode, ?string $language = null): array
    {
        $rule = Yaml::parse($yamlContent);
        $lang = $language ?? ($rule['rules'][0]['languages'][0] ?? 'php');
        $ext  = $this->extensionFor($lang);

        $tmpDir   = sys_get_temp_dir() . '/opengrep_' . Str::random(8);
        $ruleFile = "$tmpDir/rule.yaml";
        $testFile = "$tmpDir/test.$ext";

        try {
            mkdir($tmpDir, 0700, true);
            file_put_contents($ruleFile, $yamlContent);
            file_put_contents($testFile, $testCode);

            $process = new Process([
                config('opengrep.binary'),
                'scan',
                '--config', $ruleFile,
                $testFile,
                '--json',
                '--quiet',
            ]);
            $process->setTimeout(config('opengrep.timeout', 30));
            $process->run();

            $data = json_decode($process->getOutput(), true);

            if (!$data) {
                $stderr = trim($process->getErrorOutput());
                return ['error' => 'Opengrep produced no parseable output.' . ($stderr ? ' ' . $stderr : '')];
            }

            return $this->formatResults($data);
        } catch (ProcessTimedOutException) {
            return ['error' => 'Execution timed out. Your rule might be too complex.'];
        } finally {
            array_map('unlink', glob("$tmpDir/*") ?: []);
            if (is_dir($tmpDir)) {
                rmdir($tmpDir);
            }
        }
    }

    public function formatResults(array $data): array
    {
        $matches = [];

        foreach ($data['results'] ?? [] as $result) {
            $matches[] = [
                'rule_id'  => $result['check_id'],
                'message'  => $result['extra']['message'] ?? '',
                'severity' => $result['extra']['severity'] ?? 'INFO',
                'snippet'  => $result['extra']['lines'] ?? '',
                'start'    => $result['start']['line'],
                'end'      => $result['end']['line'],
            ];
        }

        return [
            'matches' => $matches,
            'errors'  => $data['errors'] ?? [],
            'total'   => \count($matches),
        ];
    }

    public function extensionFor(string $language): string
    {
        return match ($language) {
            'python'     => 'py',
            'javascript' => 'js',
            'typescript' => 'ts',
            'java'       => 'java',
            'go'         => 'go',
            'ruby'       => 'rb',
            default      => 'php',
        };
    }

    private function binaryExists(): bool
    {
        $binary = config('opengrep.binary');

        if (file_exists($binary)) {
            return true;
        }

        $cmd = PHP_OS_FAMILY === 'Windows' ? ['where', $binary] : ['which', $binary];
        $process = new Process($cmd);
        $process->run();

        return $process->isSuccessful();
    }
}
