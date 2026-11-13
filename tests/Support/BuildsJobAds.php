<?php

declare(strict_types=1);

namespace JobAds\Tests\Support;

use JobAds\JobAd;
use JobAds\Seniority;

/**
 * Small helpers for building job ads and the JSON rows they come from.
 */
trait BuildsJobAds
{
    /**
     * @param  list<string>  $skills
     */
    private function makeAd(
        int $id = 1,
        Seniority $seniority = Seniority::Mid,
        int $salaryMin = 3000,
        int $salaryMax = 6000,
        array $skills = ['php'],
    ): JobAd {
        return new JobAd(
            id: $id,
            company: 'Contoh Tech Sdn Bhd',
            title: 'PHP Developer',
            seniority: $seniority,
            location: 'Kuala Lumpur',
            salaryMin: $salaryMin,
            salaryMax: $salaryMax,
            skills: $skills,
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function makeRow(int $id = 1, array $overrides = []): array
    {
        return array_merge([
            'id' => $id,
            'company' => 'Contoh Tech Sdn Bhd',
            'title' => 'PHP Developer',
            'seniority' => 'mid',
            'location' => 'Kuala Lumpur',
            'salary_min' => 3000,
            'salary_max' => 6000,
            'skills' => ['PHP', 'Laravel'],
        ], $overrides);
    }
}
