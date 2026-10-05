# Task 006: Env-gated cross-repository transaction integration tests

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Against a real PostgreSQL and MySQL server, resolve two repositories from a container built from the real manifests and prove writes through both inside one `transaction()` roll back together when the callback throws. Skipped with a clear reason when the server env vars are not set.

## Context
- Env vars: MARKO_TEST_PGSQL_HOST/PORT/DATABASE/USERNAME/PASSWORD and MARKO_TEST_MYSQL_* equivalents
- Group: integration

## Requirements (Test Descriptions)
- [x] `it rolls back writes from two repositories when the transaction callback throws` (pgsql)
- [x] `it commits writes from two repositories when the transaction callback succeeds` (pgsql)
- [x] the same two for mysql

## Acceptance Criteria
- Tests pass locally against real servers and skip cleanly in CI

## Implementation Notes
(Left blank - filled in by programmer during implementation)
