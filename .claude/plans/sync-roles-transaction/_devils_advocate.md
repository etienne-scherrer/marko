# Devil's Advocate Review: sync-roles-transaction

## Critical (Must fix before building)

### C1. The SQLite savepoint test connection can't be reused as-is (001, 002)
`createRoleSavepointConnection()` is a global function in `packages/admin-auth/tests/Unit/Repository/RoleRepositoryTest.php` (namespace `Marko\AdminAuth\Tests\Unit\Repository`). It hard-codes a `role_permissions`-only table and a `failOnPermissionId` parameter. Task 001 needs a savepoint connection for a new `PivotSyncTest.php`, and task 002 needs one with an `admin_user_roles` table. If a worker copies the function under the same name into another file in that namespace, Pest loads both files into one process and fails with "Cannot redeclare function". If they rename it, the class ends up copied three times.
**Fix:** In task 001, move it into a fixture class `packages/admin-auth/tests/Fixtures/SqliteSavepointConnection.php`, next to the existing `SqlitePermissionConnection`. The class creates both pivot tables and takes a generic `?int $failOnId` (it throws on an INSERT that binds that id). Switch RoleRepositoryTest to the fixture. Tasks 001 and 002 both build against it.

### C2. The existing syncRoles unit test asserts the old per-row inserts (002)
`AdminUserRepositoryTest.php` `it('syncs roles for a user via syncRoles')` asserts `$queryHistory[1]['bindings'] === [1, 10]` and `$queryHistory[2]['bindings'] === [1, 20]`. That is the per-row behaviour task 002 removes. The test will go red, and nothing in task 002 tells the worker to expect that.
**Fix:** Task 002 replaces that test with the new "replaces a user's roles" and "single multi-row insert" tests.

## Important (Should fix before building)

### I1. PivotSync contract and file locations are not defined (001, 002)
Task 001 doesn't give the class's signature, its namespace or file, or the test file for the helper-level requirements. Its context lists only RoleRepositoryTest. It also doesn't say what happens to `RoleRepository::SYNC_ROWS_PER_CHUNK`.
**Fix:** Pin the contract in task 001: `Marko\AdminAuth\Repository\PivotSync`, `@internal`, not final, constructor `ConnectionInterface $connection`, method `replace(string $table, string $ownerColumn, string $relatedColumn, int $ownerId, array $relatedIds): void` with `@throws Throwable`, and `ROWS_PER_CHUNK = 500`. Remove the constant from RoleRepository. Tests go in `tests/Unit/Repository/PivotSyncTest.php`. The chunk test uses 1001 ids and expects 3 INSERT statements of 500, 500 and 1 rows.

### I2. Integration tests duplicate an existing test, and that test can't detect the bug (003)
Both `tests/Integration/MySql/PivotRepositoryTest.php` and `PgSql/PivotRepositoryTest.php` already have `it('rejects a duplicate user role')`. It seeds the user with **no** roles, so it passes before and after the fix. Adding "rejects a duplicate user role and keeps..." next to it gives two near-identical tests.
**Fix:** Rewrite the existing test in place. Seed the user with a previous role, then assert `UniqueConstraintViolationException` and that the previous role is still there. The unknown-role-id test seeds a previous role and expects `ForeignKeyConstraintViolationException`. Both drivers' exception translators map FK errors to it. Apply the same changes to the MySQL and PgSql files.

### I3. Task 004 points to the wrong docblock pattern, and the interface is missing `@throws` (004)
`RoleRepositoryInterface::syncPermissions()` has only `@param` and `@throws Throwable`. The atomicity text is on the implementation, `RoleRepository::syncPermissions()`. `AdminUserRepositoryInterface::syncRoles()` has no `@throws Throwable` at all.
**Fix:** Task 004 adds the guarantee text to both the interface and the `AdminUserRepository::syncRoles()` docblock, plus `@throws Throwable` (and `use Throwable;`) on the interface. Patterns to follow: `RoleRepository::syncPermissions()` docblock and the admin-auth.md paragraph at about line 491.

### I4. The empty-list case for syncRoles has no test (002)
`syncRoles($id, [])` is the documented way to strip all roles. After the refactor it must issue only the DELETE: no INSERT, and in particular no `INSERT ... VALUES ` with zero tuples.
**Fix:** Add the requirement `it clears all roles when given an empty role id list` to task 002.

## Minor (Nice to address)
- `PivotSync` is created on every call (`new PivotSync($this->connection)`). That's cheap and avoids changing the constructor. A lazily created property would also work, but `readonly` promotion on the parent constructor makes that awkward. Fine as planned.
- The tutorial `docs/tutorials/build-an-admin-panel.md:648` calls `syncRoles()` but says nothing about how it behaves, so it needs no change.
- Duplicate ids still throw on a unique index, which is now atomic but still an error. Deduplicating stays out of scope, as planned.

## Questions for the Team
- Should `PivotSync` dedupe ids (`array_unique`) now that the sync logic lives in one place? The issue left this as an open question, so the plan keeps it out of scope.
