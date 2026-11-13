<?php

declare(strict_types=1);

namespace JobAds\Tests;

use InvalidArgumentException;
use JobAds\JobAd;
use JobAds\Seniority;
use JobAds\Tests\Support\BuildsJobAds;
use PHPUnit\Framework\TestCase;

final class JobAdTest extends TestCase
{
    use BuildsJobAds;

    public function test_it_reads_a_json_row_into_a_job_ad(): void
    {
        $ad = JobAd::fromArray($this->makeRow(id: 7, overrides: ['seniority' => 'senior']));

        $this->assertSame(7, $ad->id);
        $this->assertSame('Contoh Tech Sdn Bhd', $ad->company);
        $this->assertSame(Seniority::Senior, $ad->seniority);
    }

    public function test_it_stores_skills_in_lowercase(): void
    {
        $ad = JobAd::fromArray($this->makeRow(overrides: ['skills' => ['Laravel', 'REST API']]));

        $this->assertSame(['laravel', 'rest api'], $ad->skills);
    }

    public function test_it_matches_a_skill_regardless_of_case(): void
    {
        $ad = $this->makeAd(skills: ['laravel']);

        $this->assertTrue($ad->hasSkill('Laravel'));
        $this->assertFalse($ad->hasSkill('vue'));
    }

    public function test_it_computes_the_midpoint_of_the_salary_range(): void
    {
        $this->assertSame(4500, $this->makeAd(salaryMin: 3000, salaryMax: 6000)->midpoint());
        $this->assertSame(4500, $this->makeAd(salaryMin: 3000, salaryMax: 6001)->midpoint());
    }

    public function test_it_rejects_a_row_with_missing_fields(): void
    {
        $row = $this->makeRow();
        unset($row['skills'], $row['salary_max']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing field(s): salary_max, skills');

        JobAd::fromArray($row);
    }

    public function test_it_rejects_an_unknown_seniority(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown seniority "lead"');

        JobAd::fromArray($this->makeRow(overrides: ['seniority' => 'lead']));
    }
}
