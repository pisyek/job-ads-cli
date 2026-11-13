# job-ads-cli

A small command-line app that lists, filters and summarises job ads.

## Development

```bash
composer install
composer check      # code style (Pint), static analysis (PHPStan), tests (PHPUnit)
```

Individual steps: `composer lint`, `composer analyse`, `composer test`. Run `composer format` to fix style issues.

CI runs the same checks on every push and pull request, plus a smoke test of `php bin/jobs` — see `.github/workflows/ci.yml`.
