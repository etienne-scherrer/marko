# Plan: MariaDB in CI

## Created
2026-10-06

## Status
completed

## Objective
Run the `marko/database-mysql` driver integration suite against MariaDB 11.8 as well as MySQL 8.4 in CI, prove the MariaDB-only `1020` retry against a real server, and make every MariaDB-specific expectation explicit.

## Related Issues
Closes #297. Relates to #257 (1020 retry), #252 and #295 (MySQL introspection and column diffs).

## Discovery Notes
Running `packages/database-mysql/tests/Integration` against a local `mariadb:11.8` on current `develop` gives 5 failures out of 39:

- `noWait()` on a row-locked row: MariaDB has no error 3572; it reports NOWAIT failures as `1205 Lock wait timeout exceeded`. The translator already maps 1205 to `LockTimeoutException`, so only the message assertion is MySQL-specific (2 tests).
- `sharedLock()->noWait()` compiles to `FOR SHARE NOWAIT`, which MariaDB rejects (syntax error). The docs already state "MariaDB does not support `FOR SHARE`". MariaDB accepts `LOCK IN SHARE MODE NOWAIT` while MySQL 8.4 rejects it, so a fix needs server-aware SQL compilation in the query builder. Left as a follow-up; the test asserts the documented limitation on MariaDB.
- MariaDB keeps integer display widths in `COLUMN_TYPE` (`int(10) unsigned`, `int(11)`, `bigint(20)`). `nativeType` is excluded from the diff, so this is only a test expectation.
- `json` columns: MariaDB stores JSON as `LONGTEXT` with an automatic `CHECK (json_valid(col))`, so the introspector reports `longtext` and every JSON column diffs forever (`MODIFY COLUMN ... JSON` on every `db:diff`). Small fix: on MariaDB, a `longtext` column with a `json_valid(\`col\`)` check constraint is reported as `json` (type and native type, no collation).
- `on update current_timestamp()` is returned raw by `onUpdateExpression()` on MariaDB (MySQL reports `CURRENT_TIMESTAMP`); normalise it like the default.

The `4025` check-constraint mapping is already exercised by `ConstraintViolationTest` (`throws a check violation naming the constraint`), which passes on MariaDB. The raw SQLSTATE assertion (`23000`) holds on both servers.

## Scope

### In Scope
- `mariadb:11.8` service + a targeted Pest step in the CI `Integration` job
- `MARKO_TEST_MYSQL_SERVER` (`mysql`/`mariadb`) so each run proves which server it hit
- Real `1020` integration test (fails with `SerializationFailureException`, retried to success by `transaction(attempts: 2)`)
- Server-aware expectations in existing integration tests
- MariaDB JSON column introspection, `on update current_timestamp()` normalisation
- Optional `mariadb` service in `tests/Integration/compose.yml`, `.claude/testing.md`, docs page

### Out of Scope
- Server-aware shared-lock modifier compilation (`LOCK IN SHARE MODE NOWAIT` on MariaDB): follow-up
- A full-suite matrix; MariaDB 10.x CI coverage

## Success Criteria
- [ ] CI `Integration` job runs `packages/database-mysql/tests/Integration` against MariaDB 11.8 and MySQL 8.4, `MARKO_INTEGRATION_REQUIRED=1`
- [ ] Real-database 1020 test passes on MariaDB
- [ ] All MySQL integration tests pass on both servers
- [ ] compose.yml MariaDB service with header instructions
- [ ] Docs state CI-tested versions
- [ ] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Server detection in the integration fixture | - | completed |
| 002 | MariaDB introspection: JSON columns and ON UPDATE spelling | - | completed |
| 003 | Real 1020 snapshot-conflict integration test | 001 | completed |
| 004 | Server-aware expectations in existing integration tests | 001, 002 | completed |
| 005 | CI job, compose service, testing guide and docs | 001, 002, 003, 004 | completed |

## Architecture Notes
- Server detection lives in the test fixture (`IntegrationDatabase`), not in production code. Its API (`isMariaDbVersion`, `isMariaDb`, `assertServer`) is fixed in task 001. A new `ServerIdentityTest` integration test enforces `MARKO_TEST_MYSQL_SERVER` in both CI runs, so a mispointed port fails instead of skipping.
- CI: `MARKO_TEST_MYSQL_SERVER: mysql` at job level; the MariaDB step overrides `MARKO_TEST_MYSQL_PORT: 3307` and `MARKO_TEST_MYSQL_SERVER: mariadb` at step level.
- The introspector already branches on `isMariaDb()`; the JSON detection reads `information_schema.CHECK_CONSTRAINTS` only on MariaDB.

## Risks & Mitigations
- MariaDB step silently hitting MySQL: `MARKO_TEST_MYSQL_SERVER` makes a mismatch a failure.
- CI time: one extra service and one Pest invocation over 7 files (~10s of tests).
