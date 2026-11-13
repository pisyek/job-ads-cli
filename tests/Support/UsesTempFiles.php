<?php

declare(strict_types=1);

namespace JobAds\Tests\Support;

/**
 * Writes throwaway job ads files and removes them after each test.
 */
trait UsesTempFiles
{
    /**
     * @var list<string>
     */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->tempFiles = [];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function writeAdsFile(array $rows): string
    {
        return $this->writeTempFile((string) json_encode($rows, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    private function writeTempFile(string $contents): string
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('job-ads-', true).'.json';
        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;

        return $path;
    }
}
