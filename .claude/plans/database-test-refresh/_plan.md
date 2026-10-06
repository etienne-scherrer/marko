# Plan: Database Test Refresh and Entity Factories

## Created
2026-10-05

## Status
completed

## Objective
Give Marko applications a supported, fast way to run database-backed tests: a migrated database booted once per process with a rolled-back transaction per test (`RefreshDatabase`), an explicit truncate alternative (`TruncateDatabase`), and explicit entity factories (`EntityFactory`).

## Related Issues
Closes #181

## Discovery Notes
- #159 shares one `ConnectionInterface` singleton; `TransactionInterface` resolves to the same instance, so one transaction covers every repository.
- #176 added savepoints, `transactionLevel()`, `afterCommit()`/`afterRollback()` (via `TransactionState`, private inside each driver connection). There is no way to run queued after-commit callbacks without committing, so `runAfterCommitCallbacks()` needs a small driver hook.
- #180's app-boot helper is `TestClient::boot()` / `TestClient::forApplication()` (`Application::boot()`). `TestClient` resets every resolved `ResettableInterface` before each request, and `PgSqlConnection::reset()`/`MySqlConnection::reset()` roll back any open transaction: an HTTP request would silently destroy the test transaction. The client needs a way to leave the connection alone.
- #170: unset `APP_ENV` = production; `db:rebuild`/`db:seed` refuse production. The refresh helper must refuse production loudly too.
- Code standards forbid traits, so the "PHPUnit trait" from the ticket becomes plain objects called from `setUp()`/`tearDown()` (or Pest `beforeEach`/`afterEach`).
- `marko/testing` must only `suggest` `marko/database`.
- Real-database tests use the existing `integration-services` harness (Postgres via `tests/Integration/compose.yml`).
- Maintainer accepted the EntityFactory middle ground (Part 2) and asked for the `database.md` factories note to be rewritten.

## Scope

### In Scope
- `Marko\Database\Connection\PendingAfterCommitInterface` + `TransactionState::runAfterCommitCallbacks()`; implemented by PgSql, MySql and ReadWrite connections
- `RequestStateResetter::reset(ResettableInterface ...$except)` and `TestClient::withoutResetting()`
- `Marko\Testing\Database\TestDatabase` (boot + migrate once per process per base path, optional fresh rebuild, production refusal, missing package/driver errors, `client()`, `seedTable()`, `getTableRowCount()`)
- `Marko\Testing\Database\RefreshDatabase` (per-test transaction, rollback, `runAfterCommitCallbacks()`)
- `Marko\Testing\Database\TruncateDatabase` (truncates entity tables only, keeps migration bookkeeping)
- `Marko\Database\Testing\EntityFactory` (`definition()`, `make`, `create`, states, `makeMany`/`createMany`, `sequence`)
- Integration tests against real Postgres for every exit criterion
- Docs: testing.md, database.md (factories note, after-commit note), READMEs, composer suggest

### Out of Scope
- Faker integration, PHPUnit traits (no traits rule), facades/global helpers
- A benchmark harness committed to the repo (benchmark numbers go in the PR body)

## Success Criteria
- [ ] Two RefreshDatabase tests inserting the same unique row both pass
- [ ] Migrations run once per process
- [ ] `transaction()` in code under test works inside RefreshDatabase
- [ ] TruncateDatabase empties entity tables and keeps the migrations table
- [ ] Clear exceptions: no database package, no driver, production environment
- [ ] EntityFactory make/create/states/count/sequence tested; create fires repository events
- [ ] Docs updated
- [ ] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Run pending after-commit callbacks without committing | - | completed |
| 002 | TestClient can leave services unreset | - | completed |
| 003 | TestDatabase: boot and migrate once per process | 002 | completed |
| 004 | RefreshDatabase | 001, 003 | completed |
| 005 | TruncateDatabase | 003 | completed |
| 006 | EntityFactory | - | completed |
| 007 | Integration tests against real Postgres | 004, 005, 006 | completed |
| 008 | Documentation, READMEs, composer suggest | 001-007 | completed |

## Architecture Notes
- Composition, no traits. Per-process state is a static map keyed by the real base path.
- `marko/testing` references `Marko\Database\*` only behind an `interface_exists()` guard that throws `DatabaseTestException::databasePackageMissing()`.
- Migrations run through `Migrator::migrate()` (committed files only, never generation).

## Risks & Mitigations
- TestClient resetting the connection rolls back the test transaction: `TestDatabase::client()` excludes the container's `ConnectionInterface`/`TransactionInterface` instances (by identity) from the reset.
- Unset APP_ENV = production, so TestDatabase refuses by default: tests (003, 007) set `APP_ENV=testing` explicitly and docs (008) make it a required setup step.
- The integration harness drops its database before every test: task 007 uses a dedicated fixture and `_refresh` database created once per process.
- A failed teardown rollback would poison later tests: RefreshDatabase resets the connection and rethrows.
- MySQL `TRUNCATE` commits implicitly: `TruncateDatabase::truncate()` refuses to run inside an open transaction.
