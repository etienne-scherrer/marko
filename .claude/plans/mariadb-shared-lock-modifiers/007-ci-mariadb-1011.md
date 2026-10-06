# Task 007: Optional MariaDB 10.11 CI step

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Add MariaDB 10.11 LTS as a second MariaDB service and step for the MySQL driver integration suite, and raise the documented floor from 10.6 to 10.11, only if the suite passes there and the extra time is small. Otherwise note it in the PR.

## Context
- Related files: .github/workflows/ci.yml, packages/docs-markdown/docs/packages/database-mysql.md, tests/Integration/compose.yml

## Requirements (Test Descriptions)
- [x] `it runs the MySQL driver integration suite against MariaDB 10.11`
- [x] `it documents MariaDB 10.11 as the tested floor`

## Acceptance Criteria
- CI Integration job green; added time small

## Implementation Notes
- The driver suite takes about 5s against MariaDB 10.11 locally, so the step was added. The only 10.11 failure was `ModifyColumnMigrationTest` creating its table with `utf8mb4_0900_ai_ci`, a MySQL collation MariaDB knows only from 11.4; it now uses `utf8mb4_unicode_ci`, which both servers have.
