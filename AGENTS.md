# AGENTS.md

## Setup and checks

- Requires PHP 8.5 (`composer.json` is `^8.5`, CI uses 8.5). The code uses 8.5-only syntax such as typed class constants (`public const int`) and readonly classes; do not "fix" it for older PHP.
- `composer install`, then `composer check` before finishing. It runs, in order: `composer lint` (Pint `--test`), `composer analyse` (PHPStan level 5 over `bin/jobs`, `src`, `tests`), `composer test` (Pest).
- `composer format` rewrites files with Pint. There is no Pint config file, so the default preset applies; `.gitattributes` enforces LF endings.
- Run a single test file or test: `php vendor/bin/pest tests/CliTest.php` and `php vendor/bin/pest tests/CliTest.php --filter=<name>`.
- CI also runs `composer validate --strict`, `php -l` on `bin/jobs` and every `src/*.php`, then smoke-tests `php bin/jobs list`, `php bin/jobs filter --skill=laravel --min-salary=5000`, and `php bin/jobs stats`.

## Structure and behavior

- Single-package PHP CLI. `bin/jobs` creates `JobAdRepository` with `data/job-ads.json` resolved from the repo root (not the current directory), then passes `$argv` to `JobAds\Cli`.
- `src/` responsibilities: `Cli` parses commands/options and formats output; `JobAd` validates/represents one JSON row; `JobAdRepository` loads and validates the file; `JobAdFilter` combines filters with AND; `SalaryStats` aggregates; `Seniority` is the enum.
- Exit codes are deliberate: `0` success, `1` missing/invalid data file (`InvalidJobAdsFile`), `2` bad command or options.
- Options must be `--name=value`; the spaced form `--name value` is rejected. `filter` only accepts `--skill` and `--min-salary`; `--min-salary` matches when `salary_max >= amount`, not `salary_min`. A seniority filter exists in code but is not exposed as a CLI option.
- `data/job-ads.json` must be a JSON array; every row needs `id`, `company`, `title`, `seniority`, `location`, `salary_min`, `salary_max`, `skills`. `seniority` is `junior`, `mid`, or `senior`. Skills are lowercased on load and matched case-insensitively.
- `stats` prints `No job ads yet.` for an empty file and omits seniority groups with no ads; `SalaryStats::fromAds([])` itself throws.

## Tests

- Both PHPUnit classes (`tests/*Test.php`, namespace `JobAds\Tests`) and Pest closures (`tests/Unit`, `tests/Feature`) are present and run by Pest. Match the style of the file you are editing rather than converting tests.
- Shared helpers: global `jobAd()` in `tests/Pest.php`; `JobAds\Tests\Support\BuildsJobAds` and `UsesTempFiles` traits. Pest files under `tests/Unit` are unnamespaced, so reference helpers/traits accordingly.
- Tests write throwaway JSON files via `UsesTempFiles`; there are no fixture files or external services.
- `phpunit.xml` fails on notices/warnings and any unexpected output. CLI tests capture only stdout, so error paths that write to STDERR are asserted by exit code only.