# Plan: Admin-Auth Canonical Keys, Slugs and Emails

## Created
2026-10-06

## Status
completed

## Objective
Make admin-auth's unique string columns (`permissions.key`, `roles.slug`, `admin_users.email`) behave the same on MySQL, MariaDB and PostgreSQL by enforcing one canonical form in PHP: a validated lowercase pattern for permission keys and role slugs, and lowercased emails.

## Related Issues
Closes #341

## Discovery Notes
- `#[Column]` cannot declare a collation, so the unique columns get the server default: case/accent-insensitive on MySQL/MariaDB, exact on PostgreSQL. PHP compares exactly (`isset`, `===`, `in_array(..., true)`).
- Issue #341 is a decision issue; its recommendation (Option A for admin-auth + Option C documentation) is implemented. Option B (entity-declared collation) is out of scope.
- Shared files with #338 (raw SQL quoting): `AdminUserRepository`, `RoleRepository` — keep edits focused on save/find/isSlugUnique.
- Integration tests live in `packages/admin-auth/tests/Integration/{MySql,PgSql}` (group `integration-services`), built on `AdminAuthSchema` from #336. CI runs the MySql directory against MariaDB too.
- Existing stored keys that differ only in case from a registered key would make sync insert a row that collides on MySQL/MariaDB and duplicates on PostgreSQL; sync repairs such a row to the canonical key instead (keeping its role assignments), so every driver gives the same result.

## Scope

### In Scope
- `IdentifierFormat` with documented permission-key and role-slug patterns
- `AdminAuthException::invalidPermissionKey()` / `invalidRoleSlug()`
- Validation in `PermissionRegistry::register()`, `PermissionDiscovery` (naming the declaring class), `PermissionRepository::save()`, `RoleRepository::save()` and `isSlugUnique()`
- `findByKey()` / `findBySlug()` return null for values outside the pattern without querying
- Email lowercasing in `AdminUserRepository::save()` and `findByEmail()`
- Sync repairs a stored case variant of a registered key
- Integration tests on MySQL, MariaDB and PostgreSQL
- Docs: `admin-auth.md` (format, normalization, upgrade note), `database.md` (collation note)

### Out of Scope
- Entity-declared collation / case sensitivity (Option B)
- Raw SQL quoting (#338)
- sessions table collation (#337)

## Success Criteria
- [x] Invalid permission keys throw `AdminAuthException` naming the key (and the declaring class from discovery)
- [x] Invalid role slugs throw on save and isSlugUnique
- [x] Emails lowercased on save and lookup; different-case login works on every driver
- [x] Integration tests show the same outcome on MySQL, MariaDB and PostgreSQL
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | IdentifierFormat and exception factories | - | completed |
| 002 | Validate keys in PermissionRegistry and PermissionDiscovery | 001 | completed |
| 003 | PermissionRepository canonical keys and case-variant repair on sync | 001 | completed |
| 004 | RoleRepository slug validation | 001 | completed |
| 005 | AdminUserRepository email normalization | - | completed |
| 006 | Integration tests on MySQL/MariaDB and PostgreSQL | 002, 003, 004, 005 | completed |
| 007 | Docs: admin-auth.md and database.md | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- Patterns use the `D` modifier so a trailing newline is not accepted.
- Permission key: `*`, or lowercase segments `[a-z0-9_-]+` separated by dots, where every segment after the first may also contain `*`.
- Role slug: lowercase segments `[a-z0-9_-]+` separated by dots (no `*`).
- Email: `mb_strtolower()`; no other normalization.
- Validation/normalization also covers the inherited `insertBatch()` in all three repositories (it bypasses `save()`).
- Sync repair: rename in place (same id) the stored row whose `strtolower(key)` equals a registered key when no exact row exists; if several variants exist (PostgreSQL only), the lowest id wins and the rest are reported unregistered.

## Risks & Mitigations
- Rejecting previously accepted keys/slugs is a visible change: called out in the PR and docs upgrade note.
- Existing mixed-case emails on PostgreSQL stop matching until lowercased: documented `UPDATE ... SET email = LOWER(email)`.
