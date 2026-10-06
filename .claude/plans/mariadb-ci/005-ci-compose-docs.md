# Task 005: CI job, compose service, testing guide and docs

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Add the `mariadb:11.8` service and a targeted Pest step to the CI `Integration` job, an optional `mariadb` service to `tests/Integration/compose.yml`, and update `.claude/testing.md` and the database-mysql docs page with the CI-tested versions.

## Context
- Related files: .github/workflows/ci.yml, tests/Integration/compose.yml, .claude/testing.md, packages/docs-markdown/docs/packages/database-mysql.md
- Pin images explicitly; keep actions at @v6 as siblings

## CI Wiring (exact)
- Service `mariadb`: `image: mariadb:11.8`, env `MARIADB_ROOT_PASSWORD: marko`, `MARIADB_DATABASE: marko_test`, `ports: - 3307:3306`, `--health-cmd "healthcheck.sh --connect --innodb_initialized"` with interval, timeout and retries matching the mysql service.
- Job-level env: add `MARKO_TEST_MYSQL_SERVER: mysql`, so the existing `composer test:integration` step proves (via ServerIdentityTest) that it hit MySQL.
- New step after `Run integration suite`, with step-level env overriding `MARKO_TEST_MYSQL_PORT: 3307` and `MARKO_TEST_MYSQL_SERVER: mariadb`. Run `php -d memory_limit=2G vendor/bin/pest -c phpunit.xml --group=integration-services packages/database-mysql/tests/Integration`. `MARKO_INTEGRATION_REQUIRED` is inherited from the job. `deadlock-contender.php` reads `MARKO_TEST_MYSQL_PORT` through `getenv()`, so it picks up the step override.
- Update the comment above the `integration` job to mention MariaDB.

## Requirements (Test Descriptions)
- [ ] CI Integration job declares a `mariadb:11.8` service on 3307 with a `healthcheck.sh --connect --innodb_initialized` health check
- [ ] CI runs `packages/database-mysql/tests/Integration` against MariaDB with `MARKO_TEST_MYSQL_SERVER=mariadb` and `MARKO_INTEGRATION_REQUIRED=1`
- [ ] compose.yml gains a `mariadb` service on `${MARIADB_PORT:-3307}` and the header documents the run
- [ ] database-mysql docs state MySQL 8.4 and MariaDB 11.8 as the CI-tested versions and describe the JSON and shared-lock differences

## Acceptance Criteria
- Integration job green on CI

## Implementation Notes
(Left blank - filled in by programmer during implementation)
