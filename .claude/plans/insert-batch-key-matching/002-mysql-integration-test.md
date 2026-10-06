# Task 002: MySQL 8.4 integration test with auto_increment_increment = 5

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove against a real MySQL 8.4 server that `insertBatch()` assigns the ids the server actually assigned when the session step is 5. MariaDB keeps its existing `RETURNING` test.

## Context
- Related files: `packages/database-mysql/tests/Integration/GeneratedPrimaryKeysTest.php`
- Patterns to follow: the MariaDB `auto_increment_increment = 5` test in the same file

## Implementation Constraints (from devil's advocate review)
- The step-5 test skips on MariaDB (`$this->isMariaDb`), which uses RETURNING and already has its own step-5 test. Assert ids equal the server's `SELECT id ... ORDER BY id` and `$ids[1] - $ids[0] === 5`.
- The explicit-ids test runs on every server. Use non-consecutive, non-monotonic ids (e.g. 10, 50, 30): with consecutive ids, `mysql_insert_id()` returns the first explicit value and the old `firstId + offset` arithmetic would pass by accident.
- Use `SET SESSION` only (never `GLOBAL`); isolation relies on the fresh connection per test that `afterEach` disconnects.

## Requirements (Test Descriptions)
- [x] `it assigns stepped auto-increment ids on insertBatch on MySQL when auto_increment_increment is 5`
- [x] `it keeps explicit auto-increment ids on insertBatch`

## Acceptance Criteria
- Tests pass on MySQL 8.4, MariaDB 11.8 and MariaDB 10.11
- Code follows code standards

## Implementation Notes
Added two tests to GeneratedPrimaryKeysTest.php. Step-5 test (skips on MariaDB) went RED when Repository used `$firstId + $offset` and GREEN with `* $step`. Explicit-ids test (10, 50, 30) guards that explicit keys are kept; it passes on the broken arithmetic too since explicit ids were never overwritten. Passes on MySQL 8.4, MariaDB 11.8, MariaDB 10.11.
