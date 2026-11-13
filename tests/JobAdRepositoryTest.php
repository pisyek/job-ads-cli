<?php

declare(strict_types=1);

namespace JobAds\Tests;

use JobAds\InvalidJobAdsFile;
use JobAds\JobAd;
use JobAds\JobAdRepository;
use JobAds\Seniority;
use JobAds\Tests\Support\BuildsJobAds;
use JobAds\Tests\Support\UsesTempFiles;
use PHPUnit\Framework\TestCase;

final class JobAdRepositoryTest extends TestCase
{
    use BuildsJobAds;
    use UsesTempFiles;

    public function test_it_loads_every_job_ad_in_the_file(): void
    {
        $path = $this->writeAdsFile([$this->makeRow(id: 1), $this->makeRow(id: 2)]);

        $ads = (new JobAdRepository($path))->all();

        $this->assertCount(2, $ads);
        $this->assertSame([1, 2], array_map(static fn (JobAd $ad): int => $ad->id, $ads));
        $this->assertSame(Seniority::Mid, $ads[0]->seniority);
    }

    public function test_it_loads_an_empty_file_as_no_job_ads(): void
    {
        $path = $this->writeAdsFile([]);

        $this->assertSame([], (new JobAdRepository($path))->all());
    }

    public function test_it_reports_a_missing_file(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'job-ads-missing-'.uniqid().'.json';

        $this->expectException(InvalidJobAdsFile::class);
        $this->expectExceptionMessage('Job ads file not found:');

        (new JobAdRepository($path))->all();
    }

    public function test_it_reports_a_file_that_is_not_valid_json(): void
    {
        $path = $this->writeTempFile('{not json');

        $this->expectException(InvalidJobAdsFile::class);
        $this->expectExceptionMessage('Job ads file is not valid JSON:');

        (new JobAdRepository($path))->all();
    }

    public function test_it_reports_a_file_that_is_not_a_list_of_job_ads(): void
    {
        $path = $this->writeTempFile('{"id": 1}');

        $this->expectException(InvalidJobAdsFile::class);
        $this->expectExceptionMessage('Job ads file must contain a JSON array of job ads:');

        (new JobAdRepository($path))->all();
    }

    public function test_it_says_which_row_is_broken(): void
    {
        $path = $this->writeAdsFile([$this->makeRow(id: 1), $this->makeRow(id: 2, overrides: ['skills' => null])]);

        $this->expectException(InvalidJobAdsFile::class);
        $this->expectExceptionMessage('Job ad #2 in');

        (new JobAdRepository($path))->all();
    }
}
