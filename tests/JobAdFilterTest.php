<?php

declare(strict_types=1);

namespace JobAds\Tests;

use JobAds\JobAd;
use JobAds\JobAdFilter;
use JobAds\Seniority;
use JobAds\Tests\Support\BuildsJobAds;
use PHPUnit\Framework\TestCase;

final class JobAdFilterTest extends TestCase
{
    use BuildsJobAds;

    public function test_an_empty_filter_keeps_every_job_ad(): void
    {
        $ads = [
            $this->makeAd(id: 1),
            $this->makeAd(id: 2, seniority: Seniority::Junior),
        ];

        $this->assertSame($ads, (new JobAdFilter)->apply($ads));
    }

    public function test_it_filters_by_skill_without_caring_about_case(): void
    {
        $ads = [
            $this->makeAd(id: 1, skills: ['laravel']),
            $this->makeAd(id: 2, skills: ['react']),
        ];

        $matches = (new JobAdFilter(skill: 'Laravel'))->apply($ads);

        $this->assertCount(1, $matches);
        $this->assertSame(1, $matches[0]->id);
    }

    public function test_it_filters_by_the_top_of_the_salary_range(): void
    {
        $ads = [
            $this->makeAd(id: 1, salaryMin: 3000, salaryMax: 4500),
            $this->makeAd(id: 2, salaryMin: 5000, salaryMax: 7000),
        ];

        $matches = (new JobAdFilter(minSalary: 5000))->apply($ads);

        $this->assertSame([2], array_map(static fn (JobAd $ad): int => $ad->id, $matches));
    }

    public function test_it_filters_by_seniority(): void
    {
        $ads = [
            $this->makeAd(id: 1, seniority: Seniority::Junior),
            $this->makeAd(id: 2, seniority: Seniority::Mid),
            $this->makeAd(id: 3, seniority: Seniority::Senior),
        ];

        $matches = (new JobAdFilter(seniority: Seniority::Mid))->apply($ads);

        $this->assertSame([2], array_map(static fn (JobAd $ad): int => $ad->id, $matches));
    }

    public function test_it_only_keeps_ads_matching_every_option(): void
    {
        $ads = [
            $this->makeAd(id: 1, skills: ['laravel'], salaryMax: 4000, seniority: Seniority::Junior),
            $this->makeAd(id: 2, skills: ['laravel'], salaryMax: 9000, seniority: Seniority::Senior),
            $this->makeAd(id: 3, skills: ['react'], salaryMax: 9000, seniority: Seniority::Senior),
        ];

        $matches = (new JobAdFilter(skill: 'laravel', minSalary: 8000, seniority: Seniority::Senior))->apply($ads);

        $this->assertSame([2], array_map(static fn (JobAd $ad): int => $ad->id, $matches));
    }

    public function test_it_returns_an_empty_list_when_nothing_matches(): void
    {
        $ads = [$this->makeAd(id: 1, skills: ['laravel'])];

        $this->assertSame([], (new JobAdFilter(skill: 'rust'))->apply($ads));
    }
}
