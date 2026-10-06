# Task 006: Repository quotes every identifier

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Every table and column name `Repository` interpolates — find, findAll, findBy, findOneBy, insertBatch (including RETURNING), insert (including RETURNING), update, delete, count (raw-SQL fallback), exists, existsBy, isColumnUnique — goes through `$this->connection->quoteIdentifier()`.

## Context
- Related files: `packages/database/src/Repository/Repository.php`, `packages/database/tests/Repository/*`, `packages/database/tests/Feature/*` (except `DatabaseTestHelperTest`, owned by task 007), `packages/admin-auth/tests/Unit/Repository/*`, and any other package test that drives a `Repository` subclass through a stub
- `isColumnUnique()` is `protected` (there is no `isUnique`); `RoleRepository` uses it, and `RoleRepositoryTest.php:99,116-117` asserts `slug = ?` / `id != ?`

## Requirements (Test Descriptions)
- [x] `it quotes the table and primary key in find`
- [x] `it quotes criteria columns in findBy and findOneBy`
- [x] `it quotes columns in insert and the RETURNING column`
- [x] `it quotes columns in insertBatch and the RETURNING column`
- [x] `it quotes SET columns and the primary key in update`
- [x] `it quotes the table and primary key in delete, count, exists and existsBy`
- [x] `it quotes the column and primary key in isColumnUnique`
- [x] `it reads the RETURNING key from the unquoted column name in the result row`

## Acceptance Criteria
- All requirements have passing tests; existing SQL assertions updated to the quoted form
- Full `composer test` green (not just `packages/database`): admin-auth, testing and other packages assert Repository SQL

## Implementation Notes
- PHPUnit `createMock()`/`createStub()` of `ConnectionInterface` returns `''` for an unconfigured `string` method, collapsing the SQL to `INSERT INTO  (, )`. Configure `quoteIdentifier` (e.g. `->method('quoteIdentifier')->willReturnCallback(...)`) on every mock/stub that reaches Repository — `RepositoryBatchInsertTest`, `RepositoryUpsertTest` and others. A shared test helper is fine.
- Hand-written stubs that choose canned rows by SQL substring (e.g. `str_contains($sql, 'FROM roles')`) will silently stop matching once the table is quoted. Search for and update them; don't rely on assertion failures alone.
- The row key from `RETURNING` is the bare column name (`$rows[0][$pkColumn]`); quote only the SQL, not the array key.
- Leave `PermissionRepository::findByGroup()` to task 008.
