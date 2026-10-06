# Devil's Advocate Review: admin-auth-entity-schema

## Critical (Must fix before building)

None. The dependency graph is right: 002 and 003 only need 001, and 004 needs 003 because 003 switches `AdminAuthSchema` to build `admin_user_roles` from the entity. The cascade and unique tests in 004 cannot pass against the old raw-SQL pivot, which has no FKs and no unique index.

## Important (Should fix before building)

1. **The worktree is ahead of the plan (all tasks).** Every task is still `pending`, but the worktree already has most of the work:
   - `src/Entity/AdminUserRole.php` and `AdminUserRoleInterface.php`
   - `tests/Unit/Entity/AdminUserRoleTest.php` and `EntitySchemaOwnershipTest.php`
   - `tests/Integration/AdminAuthTablesIntrospector.php` and `NativePasswordHasher.php`
   - `tests/Integration/{MySql,PgSql}/FreshMigrateTest.php`
   - an updated `AdminAuthSchema` with `project()`, `migrate()` and `dropMigrations()`
   - `database/migrations/` is already deleted

   One item is still missing: `Permission` does not yet have the `#[Index('idx_permissions_group', ...)]` that 001 requires. A TDD worker starting from the task text would recreate these files or clash with them. Fix: add a note to `_plan.md` telling workers to start from the existing files, check each requirement against them, and fill only the gaps.

2. **Task 003 was too vague for an independent worker.** It didn't name:
   - the `AdminAuthSchema` entity order (`AdminUserRole` must come after `Role` and `AdminUser`)
   - how generation is forced: an explicit `AppEnvironment` or `--generate`, never the shell's `APP_ENV`
   - the scoped introspector contract
   - cleanup of the shared `migrations` table (precedent: database-pgsql `ColumnCastsAndExpressionDefaultsTest`)
   - that the temp project's vendor must link only admin-auth
   - that login must run on the schema `db:migrate` created, not on `AdminAuthSchema::create()`
   - that the existing `PermissionRepositoryTest`s must keep passing on the rebuilt helper

   Fix: added these as Implementation Notes.

3. **Task 004 didn't say how to prove cascades or rejections.**
   - `RoleRepository::syncPermissions()` runs in a transaction, so a duplicate id should throw `UniqueConstraintViolationException` and leave the earlier assignments in place.
   - `AdminUserRepository::syncRoles()` is not transactional: the DELETE commits before the duplicate INSERT fails. The test should only assert the exception, not atomicity.
   - Cascades should go through the repositories' `delete()`, then count pivot rows.

   Fix: added these as Implementation Notes.

4. **Task 005 has nothing to compare against.** 002 deletes `database/migrations/` before 005 runs, so "the schema change for tables created from the old SQL" has no source. Fix: point the worker at `git show develop:packages/admin-auth/database/migrations/<file>`. List the known differences for `admin_user_roles`:
   - the old table had no `id` column
   - `user_id` and `role_id` were `INT UNSIGNED`
   - it had an extra `idx_admin_user_roles_role_id` index
   - its FKs had MySQL-generated names

   On such a table, `db:migrate` will propose destructive changes and refuse in non-interactive runs without `--force`. The worker must check what the entity diff actually produces and not guess.

## Minor (Nice to address)

- `AdminAuthTablesIntrospector` implements only `IntrospectorInterface`, not `ExpressionDefaultMatcherInterface`, so `ExpressionDefaultCanonicalizer` returns early. This differs from the production path. It is harmless today because no admin-auth column has an expression default. Delegating the matcher would keep "production path" accurate if one is added later.
- On PostgreSQL, neither `admin_user_roles.role_id` nor `role_permissions.permission_id` has a leading index; the old migration had `idx_admin_user_roles_role_id`. Deleting a role or permission scans the pivot. MySQL creates FK indexes automatically, so this affects PostgreSQL only.
- `composer test` runs `--parallel` and does not exclude `integration-services`. A developer with `MARKO_TEST_MYSQL_HOST` exported will run three admin-auth files in parallel, each dropping and creating the same tables and `migrations` in `marko_test`. This can be flaky locally. CI runs `test:integration` serially, so CI is unaffected.
- 005 could also check `docs/tutorials/build-an-admin-panel.md`. Its manual `INSERT INTO admin_user_roles` still works with the new `id` column, but it is a natural place to say the table now comes from `db:migrate`.

## Questions for the Team

- Should `AdminUserRepository::syncRoles()` become transactional like `syncPermissions()`? It is out of scope here; maybe a follow-up issue.
- Should there be an upgrade test: tables created from the old MySQL SQL, then `db:migrate`? Or is a docs note enough for 1.0?
- Add `#[Index]` on the `role_id` / `permission_id` pivot columns for PostgreSQL cascade performance?
