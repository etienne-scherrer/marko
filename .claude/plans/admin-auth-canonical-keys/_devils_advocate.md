# Devil's Advocate Review: admin-auth-canonical-keys

## Critical (Must fix before building)

None. Note: `packages/admin-auth/src/IdentifierFormat.php` and the `invalidPermissionKey(string $key, ?string $declaringClass = null)` / `invalidRoleSlug(string $slug)` factories already exist in the worktree (no tests yet). Task 001 should add the tests and adjust the code if needed, not recreate it. Its API is now stated in task 001 so 002-005 build against fixed names.

## Important (Should fix before building)

1. **Task 002 — discovery's catch block mislabels invalid keys.** `PermissionDiscovery::registerFromDefinitions()` wraps every `AdminAuthException` from `register()` as `permissionAlreadyRegistered()`. If an invalid key only fails inside `register()`, the user is told the key "is already registered". Validation also has to run in the first pass, before any `register()` call, or "registers no permissions from any section" fails because earlier sections were already registered. Fix: validate in the first loop (next to the duplicate check) with `invalidPermissionKey($id, $definition->className)`. Spelled out in task 002.

2. **Tasks 003/004/005 — `insertBatch()` bypasses `save()`.** `RepositoryInterface::insertBatch()` is inherited by all three repositories and issues a multi-row INSERT without calling `save()`. That leaves a way around key/slug validation and email lowercasing. Fix: apply the same check or normalization to every entity in `insertBatch()` before delegating to the parent. For 003/004 the whole batch must be rejected before any SQL runs. Added as requirements.

3. **Task 003 — `PermissionRepository` has no `save()` override.** The worker has to add one: an `instanceof Permission` guard, then validate, then `parent::save()`. The sync loop calls `$this->save()` for the repair as well, so the canonical key passes. This is now stated in the task.

4. **Task 003 — on PostgreSQL several stored case variants with no exact match break determinism.** For example, `Blog.View` and `BLOG.VIEW` are both stored and `blog.view` is registered. That can only happen on PG, but which row gets renamed has to be deterministic. Fix: rename the variant with the lowest id and report the others as unregistered. Also: a renamed row must not appear in `unregistered`, and its role assignments stay on the same id. Added requirements.

5. **Task 006 — non-canonical rows cannot be seeded through `save()`.** After 003/004 land, `AdminAuthSchema::permission()` and the repository `save()` reject non-canonical keys. To set up a stored case variant, the integration test has to INSERT with raw SQL through the connection. The duplicate-email test should assert a database unique-violation exception, not an `AdminAuthException`. Both points are now in the task.

6. **Task 007 — the upgrade SQL fails on PostgreSQL duplicates.** `UPDATE admin_users SET email = LOWER(email)` violates the unique index if `a@x.com` and `A@x.com` both exist, which PG allows. The docs must show a query that finds such duplicates first (`GROUP BY LOWER(email) HAVING COUNT(*) > 1`). They also need queries that find existing non-canonical role slugs and permission keys, because roles with slugs like `Editor` will start throwing on the next `save()`. Added to task 007.

## Minor (Nice to address)

- Repair matches only case variants (`strtolower($stored) === $registered`). On MySQL/MariaDB, accent variants (`blog.vïew`) and PAD SPACE trailing-space variants still collide on INSERT. Only legacy rows written before this change are affected. The plan's claim that "every driver gives the same result" is slightly overstated, so the docs could mention it.
- The 002-007 "Patterns to follow" lines are copy-pasted ("MarkoException static factories"). They are harmless but not useful for task 005 or 007.
- Task 007 has "it documents ..." test names. A doc task has no Pest tests, so the worker should treat these as a checklist.
- Stored wildcard keys with uppercase letters (`Blog.*`) are still reported as wildcard grants and match nothing on any driver. That is consistent across drivers but silent. The sync report could flag them.

## Questions for the Team

- Should `PermissionRegistry::register()` / `#[AdminPermission]` accept wildcard keys (`blog.*`)? `PERMISSION_KEY_PATTERN` allows them, but the repository docs say wildcards are "never registered by #[AdminPermission]". A stricter registrable-key check would be one more pattern. The plan leaves it permissive.
- Should `super_admin_role` config values be validated against `ROLE_SLUG_PATTERN` at boot? A value like `Super-Admin` would never match a stored slug.
