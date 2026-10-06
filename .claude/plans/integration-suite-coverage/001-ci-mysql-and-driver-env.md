# Task 001: CI workflow, compose and CiWorkflowTest for MySQL + driver env

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a `mysql:8.4` service to the Integration job with a health check, add `pdo_mysql`, export `MARKO_TEST_PGSQL_*` (existing Postgres service, own `marko_test` database created by a step) and `MARKO_TEST_MYSQL_*`. Mirror it in `tests/Integration/compose.yml`.

## Context
- Related files: `.github/workflows/ci.yml`, `tests/CiWorkflowTest.php`, `tests/Integration/compose.yml`
- Patterns to follow: existing postgres/redis service blocks; actions pinned at `@vN`

## Requirements (Test Descriptions)
- [x] `it runs a mysql service with a health check in the integration job`
- [x] `it installs pdo_mysql alongside pdo_pgsql in the integration job`
- [x] `it points the pgsql driver integration tests at the postgres service with their own database`
- [x] `it points the mysql driver integration tests at the mysql service`
- [x] `it defines mysql in the local integration compose file`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
