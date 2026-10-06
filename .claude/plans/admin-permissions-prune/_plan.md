# Plan: Admin Permissions Prune

## Created
2026-10-06

## Status
completed

## Objective
Make `admin-auth:permissions:sync` keep the `permissions` table in step with the registered permissions: update labels and groups, report unregistered (stale) rows with role counts, and delete them only on an explicit `--prune` that follows the shared destructive-command policy. Dispatch `PermissionsSynced`.

## Related Issues
Closes #330

## Discovery Notes
- `PermissionRepository::syncFromRegistry()` only inserts and returns an int. `SyncPermissionsCommand` takes no options and dispatches nothing. `PermissionsSynced(createdCount, totalCount, timestamp)` exists but nobody dispatches it.
- `DestructiveCommandGuard` (marko/database) refuses production even with `--force`, allows development/testing without asking, and asks for confirmation with `--force` elsewhere when interactive. It is a readonly service shared by db:rebuild/reset/rollback/seed, so the opt-in is a per-call named argument, not a constructor flag.
- #323/#331 merged: `ConnectionInterface::quoteIdentifier()` exists; `key` and `group` are reserved words and must be quoted in raw SQL. Real-database tests for `PermissionRepository` already exist under `tests/Integration/{MySql,PgSql}`; CI runs the MySql ones a second time against MariaDB 11.8.
- `role_permissions.permission_id` has an `onDelete: CASCADE` FK, but prune deletes role_permissions rows explicitly inside the transaction.
- Keys with `*` are wildcard grants (`catalog.*`, `*`), never registered by `#[AdminPermission]`, so they are never reported as stale nor pruned.
- `RoleRepositoryTest` already uses an in-memory SQLite PDO connection double; the unit tests reuse that approach through a shared fixture so sync/prune behaviour runs against real SQL in `composer test`.

## Scope

### In Scope
- Guard opt-ins `allowInProduction` and `confirmInDevelopment` on `DestructiveCommandGuard::check()`
- `PermissionSyncResult` and `UnregisteredPermission` value objects; `syncFromRegistry()` returns the result (created/updated keys, unregistered rows with role counts, wildcard keys kept)
- `findUnregistered()` and `pruneUnregistered()` on `PermissionRepositoryInterface`
- `PermissionsSynced` gains updated/unregistered/pruned counts (appended, defaulted, backward compatible)
- `SyncPermissionsCommand`: report, `--prune`, `--force`, confirmation, event
- Real-database tests on PostgreSQL, MySQL and MariaDB
- Docs: admin-auth.md, database.md

### Out of Scope
- Soft-delete / stale marking
- A separate prune command
- Any change to boot (still never touches the database)

## Success Criteria
- [x] Plain sync inserts missing rows, updates changed labels/groups, lists stale keys with role counts, deletes nothing
- [x] `--prune` deletes stale permissions and their role assignments in one transaction, never keys containing `*`, and reports what it removed
- [x] Outside development/testing `--prune` is refused without `--force`; with `--force` it confirms when interactive and runs when not
- [x] `PermissionsSynced` dispatched by the command
- [x] All tests passing; `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | DestructiveCommandGuard opt-ins | - | completed |
| 002 | Sync result value objects and SQLite test connection | - | completed |
| 003 | syncFromRegistry updates and reports unregistered rows | 002 | completed |
| 004 | pruneUnregistered deletes stale rows and role assignments | 002, 003 | completed |
| 005 | PermissionsSynced carries updated/unregistered/pruned counts | - | completed |
| 006 | SyncPermissionsCommand report, --prune, --force, event | 001, 003, 004, 005 | completed |
| 007 | Real-database tests (PostgreSQL, MySQL, MariaDB) | 003, 004 | completed |
| 008 | Documentation | 001, 006 | completed |

## Architecture Notes
- The guard keeps the whole environment policy: `check(..., allowInProduction: true, confirmInDevelopment: true)`. Production with `allowInProduction` behaves like any other non-development environment (needs `--force`, asks when interactive).
- `pruneUnregistered()` recomputes the stale set inside the transaction, so it deletes exactly the rows that are unregistered at that moment; wildcard keys are filtered before any DELETE.
- Raw SQL quotes `key`/`group` through `$this->connection->quoteIdentifier()`.
- The result objects are readonly classes with public promoted properties (exact contract in task 002). Role counts use `COUNT(DISTINCT role_id)`.
- Task 003 owns keeping the suite green across the return-type change: it adapts `SyncPermissionsCommand` minimally and updates the existing command, repository and interface tests. Task 006 then rewrites the command.
- The command gains `DestructiveCommandGuard`, `EventDispatcherInterface` and `ClockInterface` (all autowired) and declares `flags: ['prune', 'force']`. `PermissionsSynced` fires after every run, including a refused or cancelled prune (`prunedCount: 0`).
- Integration tests build roles/permissions/role_permissions/admin_users from entities. `admin_user_roles` (no entity; its migration is MySQL-only DDL) uses portable raw DDL. Tables drop in reverse FK order.

## Risks & Mitigations
- Changing the `syncFromRegistry()` return type is a breaking interface change: called out in the PR; the only caller is the command.
- `DELETE ... IN (...)` with many ids: chunk ids (500 per statement), matching `RoleRepository::SYNC_ROWS_PER_CHUNK`.
