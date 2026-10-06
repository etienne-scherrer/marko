# Devil's Advocate Review: admin-permissions-prune

## Critical (Must fix before building)

1. **Task 003 breaks the command and existing tests the moment the return type changes.** `SyncPermissionsCommand::execute()` does `$registered - $created` on the return value (TypeError on an object). `SyncPermissionsCommandTest` uses `->willReturn(1)` / `willReturn(2)`, which PHPUnit rejects when the return type is `PermissionSyncResult`. `PermissionRepositoryTest` has `returns the number of permissions created by syncFromRegistry` (expects int 1) and `syncs permissions ... creating new and preserving existing` (expects two per-key SELECTs, which contradicts "reads the table once"). `PermissionRepositoryInterfaceTest` line 70 asserts the int return. Task 006 depends on 003, so between them the suite is red. Fix: task 003 owns updating these tests and adapting the command minimally (read the created count from the result, same output line). Task 006 then rewrites the command.

2. **Task 007 cannot create every table it needs "from the entities".** `admin_user_roles` has no entity; `AdminUserRepository::getRolesForUser()` joins it, so the AdminUserProvider test needs it. The migrations in `database/migrations/` are MySQL-only DDL (`INT UNSIGNED`, inline `INDEX`) and won't run on PostgreSQL. The existing `beforeEach` also drops only `permissions`, and that fails on both engines once `role_permissions` holds an FK to it (for example, after a crashed run). Fix: build `roles`, `permissions`, `role_permissions`, `admin_users` from their entities through SchemaBuilder in dependency order. Create `admin_user_roles` with quoted, portable raw DDL. Drop in reverse dependency order before and after each test.

## Important (Should fix before building)

3. **Task 002/003/006 contract for `PermissionSyncResult` is not pinned down.** Workers need exact member names. Fix: define both as `readonly` classes with promoted public properties (matching `RegisteredPermission`). Add count methods on the result.

4. **Task 006 doesn't name the new constructor dependencies or the event/exit semantics.** The command needs `DestructiveCommandGuard`, `EventDispatcherInterface` (core, bound in Application) and `Psr\Clock\ClockInterface` (bound by marko/clock, transitively required via marko/database). `#[Command]` must declare `flags: ['prune', 'force']`. The plan doesn't say whether the event fires when the prune is refused or cancelled. Sync has already committed by then, so it should fire with `prunedCount: 0`. It also doesn't say what `totalCount` means or which exit code applies. Fix: spelled out in 006 with tests.

5. **Task 002 shared fixture as a global helper function risks redeclaration.** Pest loads every test file into one process. `RoleRepositoryTest` already declares `createRoleSavepointConnection()`. Fix: make the fixture an autoloaded class under `Marko\AdminAuth\Tests\Fixtures`. It needs a `permissions` table (id, key, label, group, created_at) and a `role_permissions` table. Add a failure-injection hook so task 004 can test rollback.

6. **Task 003/004 role counts.** The entity-built `role_permissions` (task 007) has no unique index on (role_id, permission_id), so duplicate rows inflate `COUNT(*)`. Fix: `COUNT(DISTINCT role_id)`.

## Minor (Nice to address)

- MySQL/MariaDB compare `key` case-insensitively under the default collation. Comparing keys in PHP is case-sensitive. If a registered key differs from a stored key only in case, sync will try an INSERT that hits the unique index on MySQL, and the stored row will be reported as stale and pruned. Rare, because keys are lower-case by convention.
- `admin-auth.md` (around line 209) shows a hand-written `new PermissionsSynced(...)` dispatch example. Task 008 should replace it, because the command now dispatches the event.
- When `allowInProduction` is set, the production branch must not print "never allowed in production, even with --force". Task 001 should assert the message.
- `pruneUnregistered()` deletes with raw SQL, so no `EntityDeleting`/`EntityDeleted` events fire. That's acceptable, but the docblock should say so.

## Questions for the Team

- When `--prune` is refused, should the command return 1 even though the non-destructive sync has already been committed? The plan assumes yes.
- Should prune also run if the confirmation prompt has a default of "yes"? The plan assumes the guard's default (no).
