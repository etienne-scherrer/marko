# Plan: Sync Roles Transaction

## Created
2026-10-06

## Status
completed

## Objective
Make `AdminUserRepository::syncRoles()` atomic and batched, with the same transaction semantics as `RoleRepository::syncPermissions()`, so a failing insert never strips an admin user of their roles.

## Related Issues
Closes #347

## Discovery Notes
- `syncRoles()` runs `DELETE` then one `INSERT` per role with no transaction; an unknown role id (FK violation) or a duplicate id (unique index) leaves the user with no roles.
- `RoleRepository::syncPermissions()` already does it right: `DELETE` + chunked multi-row `INSERT` (500 rows per statement) inside `transaction()` when the connection is a `TransactionInterface` (savepoint when nested), direct fallback otherwise.
- The code standards forbid traits, so the shared logic goes into a small composed class (`PivotSync`) that both repositories construct around their connection. It owns the chunk size, so nothing is copied.
- #338 (raw SQL identifier quoting) also edits these pivot statements; after this change they live in one place.
- Integration harness from #340 (`AdminAuthSchema`, `tests/Integration/{MySql,PgSql}/PivotRepositoryTest.php`) creates the pivots with their foreign keys and unique indexes.

## Scope

### In Scope
- `PivotSync` helper: replace a pivot owner's rows atomically with chunked multi-row inserts
- `RoleRepository::syncPermissions()` and `AdminUserRepository::syncRoles()` both use it
- Unit tests: transaction wrap, rollback on failure, savepoint inside caller's transaction, caller can still commit, fallback without transactions, batching
- Real-database tests (MySQL 8.4, MariaDB, PostgreSQL): unknown role id and duplicate id leave the previous roles intact
- Interface docblock and `admin-auth.md`

### Out of Scope
- Deduplicating `$roleIds` / `$permissionIds` (left as a maintainer question in the issue)
- Quoting the pivot identifiers (#338)

## Success Criteria
- [x] `syncRoles()` runs delete + inserts in one `transaction()`; a failing insert leaves the previous roles
- [x] Inside a caller's transaction only the sync's changes roll back and the caller can commit
- [x] Still syncs on a connection without transactions
- [x] Inserts are batched in multi-row statements
- [x] Integration tests on MySQL/MariaDB and PostgreSQL
- [x] Interface docblock and docs describe the guarantee
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | PivotSync helper used by RoleRepository, plus admin_user_roles in the shared SQLite fixture | - | completed |
| 002 | Transactional, batched syncRoles() (replaces the per-row unit test) | 001 | completed |
| 003 | Real-database pivot tests for a failed syncRoles() (rewrites the existing duplicate-role test) | 002 | completed |
| 004 | Interface/implementation docblocks (`@throws Throwable`) and admin-auth docs | 002 | completed |

## Architecture Notes
- PivotSync contract: `PivotSync::replace(string $table, string $ownerColumn, int $ownerId, string $relatedColumn, array $relatedIds): void`, `ROWS_PER_CHUNK = 500` (moved from RoleRepository). Full signature in task 001.
- Unit tests share the existing `tests/Fixtures/SqlitePermissionConnection.php`, extended with the `admin_user_roles` table (see task 001 notes). A global helper function copied between test files in the same namespace would fail with a redeclare error.
- No traits: `PivotSync` is composed (`new PivotSync($this->connection)`), not mixed in. It is not part of the public repository contracts (`@internal`), so it adds no constructor dependency to either repository.
- Table and column names passed to `PivotSync` are code constants, never user input.

## Risks & Mitigations
- Merge conflict with #338 on the pivot SQL: the statements now live in one helper, so the rebase touches one place.
