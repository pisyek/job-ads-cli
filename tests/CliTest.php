<?php

declare(strict_types=1);

namespace JobAds\Tests;

use JobAds\Cli;
use JobAds\JobAdRepository;
use JobAds\Tests\Support\BuildsJobAds;
use JobAds\Tests\Support\UsesTempFiles;
use PHPUnit\Framework\TestCase;

final class CliTest extends TestCase
{
    use BuildsJobAds;
    use UsesTempFiles;

    public function test_it_lists_every_job_ad(): void
    {
        [$status, $output] = $this->runCli([$this->makeRow(id: 1), $this->makeRow(id: 2)], ['list']);

        $this->assertSame(Cli::SUCCESS, $status);
        $this->assertStringContainsString('PHP Developer', $output);
        $this->assertStringContainsString('2 job ads.', $output);
    }

    public function test_it_lists_no_job_ads_when_the_file_is_empty(): void
    {
        [$status, $output] = $this->runCli([], ['list']);

        $this->assertSame(Cli::SUCCESS, $status);
        $this->assertStringContainsString('0 job ads.', $output);
    }

    public function test_it_filters_job_ads_by_skill_and_salary(): void
    {
        $rows = [
            $this->makeRow(id: 1, overrides: ['skills' => ['laravel'], 'salary_max' => 4000]),
            $this->makeRow(id: 2, overrides: ['skills' => ['laravel'], 'salary_max' => 9000]),
            $this->makeRow(id: 3, overrides: ['skills' => ['react'], 'salary_max' => 9000]),
        ];

        [$status, $output] = $this->runCli($rows, ['filter', '--skill=laravel', '--min-salary=5000']);

        $this->assertSame(Cli::SUCCESS, $status);
        $this->assertStringContainsString('1 of 3 job ads match.', $output);
    }

    public function test_it_shows_salary_statistics_by_seniority(): void
    {
        $rows = [
            $this->makeRow(id: 1, overrides: ['seniority' => 'junior', 'salary_min' => 3000, 'salary_max' => 5000]),
            $this->makeRow(id: 2, overrides: ['seniority' => 'senior', 'salary_min' => 8000, 'salary_max' => 12000]),
        ];

        [$status, $output] = $this->runCli($rows, ['stats']);

        $this->assertSame(Cli::SUCCESS, $status);
        $this->assertStringContainsString('Job ads:          2', $output);
        $this->assertStringContainsString('Salary range:     RM 3,000 - 12,000 per month', $output);
        $this->assertStringContainsString('Average midpoint: RM 7,000 per month', $output);
        $this->assertStringContainsString('1 ads, average midpoint RM 4,000', $output);
        $this->assertStringContainsString('1 ads, average midpoint RM 10,000', $output);
    }

    public function test_it_says_there_are_no_job_ads_before_the_statistics(): void
    {
        [$status, $output] = $this->runCli([], ['stats']);

        $this->assertSame(Cli::SUCCESS, $status);
        $this->assertStringContainsString('No job ads yet.', $output);
    }

    public function test_it_shows_the_usage_for_the_help_command(): void
    {
        [$status, $output] = $this->runCli([], ['help']);

        $this->assertSame(Cli::SUCCESS, $status);
        $this->assertStringContainsString('Usage: php bin/jobs <command> [options]', $output);
    }

    public function test_it_asks_for_a_command_when_none_is_given(): void
    {
        [$status] = $this->runCli([$this->makeRow()]);

        $this->assertSame(Cli::USAGE_ERROR, $status);
    }

    public function test_it_rejects_an_unknown_command(): void
    {
        [$status] = $this->runCli([$this->makeRow()], ['publish']);

        $this->assertSame(Cli::USAGE_ERROR, $status);
    }

    public function test_it_rejects_an_option_without_a_value(): void
    {
        [$status] = $this->runCli([$this->makeRow()], ['filter', '--skill']);

        $this->assertSame(Cli::USAGE_ERROR, $status);
    }

    public function test_it_rejects_an_unknown_filter_option(): void
    {
        [$status] = $this->runCli([$this->makeRow()], ['filter', '--level=mid']);

        $this->assertSame(Cli::USAGE_ERROR, $status);
    }

    public function test_it_rejects_a_salary_that_is_not_a_whole_number(): void
    {
        [$status] = $this->runCli([$this->makeRow()], ['filter', '--min-salary=-5000']);

        $this->assertSame(Cli::USAGE_ERROR, $status);
    }

    public function test_it_fails_when_the_job_ads_file_is_missing(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'job-ads-missing-'.uniqid().'.json';

        $this->assertSame(Cli::FAILURE, (new Cli(new JobAdRepository($path)))->run(['bin/jobs', 'list']));
    }

    /**
     * Runs the CLI against a throwaway job ads file and returns its exit code and standard output.
     *
     * Messages written to standard error are not captured, only the exit code tells them apart.
     *
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $arguments
     * @return array{0: int, 1: string}
     */
    private function runCli(array $rows, array $arguments = []): array
    {
        $cli = new Cli(new JobAdRepository($this->writeAdsFile($rows)));

        ob_start();
        $status = $cli->run(['bin/jobs', ...$arguments]);

        return [$status, (string) ob_get_clean()];
    }
}
