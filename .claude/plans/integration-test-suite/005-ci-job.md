# Task 005: Integration CI Job

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add a fourth CI job, "Integration", with health-checked `postgres:17` and `redis:7` services, PHP 8.5 with
`pdo_pgsql`, running `composer test:integration` in required mode. Extend `tests/CiWorkflowTest.php`.

## Context
- Related files: `.github/workflows/ci.yml`, `tests/CiWorkflowTest.php`
- Actions pinned at `actions/checkout@v6`, `shivammathur/setup-php@v2`, `ramsey/composer-install@v3`

## Requirements (Test Descriptions)
- [x] `it runs the integration-services group in its own job against postgres and redis services`
- [x] `it health-checks the integration services before running the suite`
- [x] `it fails the integration job instead of skipping when services are unreachable`
- [x] `it exposes a composer test:integration script scoped to the integration-services group`

## Acceptance Criteria
- Existing CiWorkflowTest assertions (PHP pin per job, pinned actions, no test:all) still pass

## Gotchas (from devil's advocate review)
- CiWorkflowTest checks that `substr_count("php-version: '8.5'")` equals the count of `runs-on: ubuntu-latest`, so the
  new job uses that exact string.
- The job runs on the runner host, not in a container. Map the ports `5432:5432` and `6379:6379` and set
  `DB_HOST=127.0.0.1` and `REDIS_HOST=127.0.0.1`. cache-redis ignores config and always connects to
  `127.0.0.1:6379` (#166), so Redis must be on that address.
- Set `MARKO_INTEGRATION_REQUIRED=1` and the DB credentials in the job `env:`. Add `pdo_pgsql` to `extensions`.

## Implementation Notes
(Left blank - filled in by programmer during implementation)
