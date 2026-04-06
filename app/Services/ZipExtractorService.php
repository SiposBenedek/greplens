<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ZipExtractorService
{
    /**
     * Validate, extract the zip to a unique temp directory, and return the path.
     *
     * @throws \RuntimeException
     */
    public function extract(string $zipPath): string
    {
        $extractPath = storage_path('app/temp/rule-import-' . Str::uuid());

        File::makeDirectory($extractPath, 0755, true);

        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            File::deleteDirectory($extractPath);
            throw new \RuntimeException('Could not open zip file.');
        }

        $this->validateEntries($zip);

        $zip->extractTo($extractPath);
        $zip->close();

        return $extractPath;
    }

    /**
     * Delete the temp directory created during extraction.
     */
    public function cleanup(string $path): void
    {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    /**
     * Reject unsafe zip entries: path traversal, absolute paths, and zip bombs.
     *
     * @throws \RuntimeException
     */
    private function validateEntries(ZipArchive $zip): void
    {
        $totalUncompressed = 0;
        $maxUncompressed   = 200 * 1024 * 1024; // 200 MB

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat['name'];

            if (
                str_contains($name, '..')
                || str_starts_with($name, '/')
                || str_starts_with($name, '\\')
            ) {
                $zip->close();
                throw new \RuntimeException("Zip contains unsafe path: {$name}");
            }

            $totalUncompressed += $stat['size'];

            if ($totalUncompressed > $maxUncompressed) {
                $zip->close();
                throw new \RuntimeException('Zip exceeds maximum uncompressed size (200 MB).');
            }
        }
    }
}