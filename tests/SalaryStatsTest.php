<?php

declare(strict_types=1);

namespace JobAds\Tests;

use InvalidArgumentException;
use JobAds\SalaryStats;
use JobAds\Seniority;
use JobAds\Tests\Support\BuildsJobAds;
use PHPUnit\Framework\TestCase;

final class SalaryStatsTest extends TestCase
{
    use BuildsJobAds;

    public function test_it_summarises_the_salary_ranges_of_a_group(): void
    {
        $stats = SalaryStats::fromAds([
            $this->makeAd(seniority: Seniority::Junior, salaryMin: 3000, salaryMax: 6000),
            $this->makeAd(seniority: Seniority::Senior, salaryMin: 5000, salaryMax: 9000),
        ]);

        $this->assertSame(2, $stats->count);
        $this->assertSame(3000, $stats->lowest);
        $this->assertSame(9000, $stats->highest);
        $this->assertSame(5750, $stats->averageMidpoint);
    }

    public function test_it_handles_a_single_job_ad(): void
    {
        $stats = SalaryStats::fromAds([$this->makeAd(salaryMin: 2800, salaryMax: 3800)]);

        $this->assertSame(1, $stats->count);
        $this->assertSame(2800, $stats->lowest);
        $this->assertSame(3800, $stats->highest);
        $this->assertSame(3300, $stats->averageMidpoint);
    }

    public function test_it_refuses_to_summarise_an_empty_group(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot compute salary stats for zero job ads.');

        SalaryStats::fromAds([]);
    }
}
