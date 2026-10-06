# Plan: Database-Generated Primary Keys

## Created
2026-10-06

## Status
completed

## Objective
Let `Repository::save()` and `insertBatch()` persist an entity whose primary key the database generates (e.g. `DEFAULT gen_random_uuid()`), declared explicitly with `#[Column(generated: true)]`, reading the key back with `INSERT ... RETURNING` where the connection supports it, and fail loudly with a `RepositoryException` everywhere else.

## Related Issues
Closes #305

## Discovery Notes
- `EntityHydrator::extract()` reads every mapped property, so an unset `public string $id` raises a raw PHP `Error`.
- `Repository::insert()` only strips a null auto-increment key and only reads back an auto-increment key via `lastInsertId(): int`.
- `Repository::insertBatch()` already uses `RETURNING <pk>` for auto-increment keys, but gates it on `driverName() === 'pgsql'`.
- `ConnectionInterface` implementers in `src/`: `MySqlConnection`, `PgSqlConnection`, `ReadWriteConnection` (wraps the write connection). ~55 test stubs across packages implement the interface too.
- #253/#301 merged: `Column::$default` accepts strings, `Expression` and `Literal`.
- #306/#307 (introspector/generator) run in parallel; this plan must not touch the driver SQL generators or introspectors.

## Scope

### In Scope
- `#[Column(generated: true)]`, carried into `PropertyMetadata::$isGenerated`, validated at parse time (primary key only, not with `autoIncrement`, requires a `default`).
- `ConnectionInterface::supportsReturning(): bool` (breaking): PgSql `true`, MySQL `false`, ReadWrite delegates to the write connection. All test stubs updated.
- `EntityHydrator::extract()` omits an uninitialized primary key instead of erroring.
- `Repository::insert()` and `insertBatch()`: omit an unset/null generated key, append `RETURNING <pk>` and set the value through the property cast; throw `RepositoryException` on connections without RETURNING; throw `RepositoryException` for an unset/null key that is neither auto-increment nor generated.
- `insertBatch()` RETURNING path switches from `driverName() === 'pgsql'` to `supportsReturning()`.
- PostgreSQL and MySQL integration tests; docs pages (database, database-pgsql, database-mysql).

### Out of Scope
- A MariaDB `RETURNING` path (needs #297 for CI coverage); MariaDB keeps the MySQL behaviour.
- Changing `insert()`'s auto-increment read-back (still `lastInsertId()`).
- Schema generators/introspectors (#306, #307).
- Upsert reading generated keys back (upsert never sets ids).

## Success Criteria
- [x] A `generated: true` uuid key saves on PostgreSQL without being set and holds the generated UUID afterwards; `find($id)` returns it
- [x] `insertBatch()` on PostgreSQL gives each entity its own generated key in insert order
- [x] On MySQL an unset generated key throws `RepositoryException` naming the entity and telling you to set the key in PHP; a set key saves normally
- [x] An unset or null non-generated key throws `RepositoryException`
- [x] `generated: true` on a non-key column, with `autoIncrement`, or without `default` fails at parse time
- [x] All tests passing
- [x] Code follows project standards; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | `generated` column metadata and parse-time validation | - | completed |
| 002 | `ConnectionInterface::supportsReturning()` and implementations | - | completed |
| 003 | Hydrator omits an uninitialized primary key on extract | - | completed |
| 004 | `Repository::insert()` generated keys and loud unset-key errors | 001, 002, 003 | completed |
| 005 | `Repository::insertBatch()` generated keys via `supportsReturning()` | 001, 002, 003, 004 | completed |
| 006 | PostgreSQL and MySQL integration tests | 001, 002, 003, 004, 005 | completed |
| 007 | Docs pages and READMEs | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- Name the opt-in `generated` (the ticket's first choice). It sits next to `autoIncrement` and reads as "the database generates this key".
- The capability lives on `ConnectionInterface` rather than a separate interface because `ReadWriteConnection` wraps any connection and can only answer by delegating; a capability interface would still need a runtime bool. This is a breaking change for third-party drivers, so the PR gets the `breaking` label.
- The key checks run inside `insert()` (after `EntityCreating`), so an observer can still assign the key before the insert.
- The read-back value goes through `EntityHydrator::toPhpValue()` so the property's cast applies (a uuid stays a `string`, an int key becomes `int`).

## Risks & Mitigations
- Many test stubs implement `ConnectionInterface`: add `supportsReturning()` mechanically to each and run the full suite.
- ReadWriteConnection routes `query()` by statement type: an `INSERT ... RETURNING` goes through `query()`, which already routes INSERT to the write connection. It does not set sticky-write there (only `execute()` does), so task 002 makes write statements through `query()` sticky to keep read-your-writes after `save()`.
- A repo-root stub (`tests/Integration/QueryBuilderRawConsistencyTest.php`) also implements `ConnectionInterface`; task 002 covers it.
- `upsert()` shares `extractBatchRow()`: it strips unset generated keys but never throws the RETURNING/unset-key errors (task 005).
- Throwing for an unset/null non-generated key is a behaviour change: task 004 runs the full suite across packages.
