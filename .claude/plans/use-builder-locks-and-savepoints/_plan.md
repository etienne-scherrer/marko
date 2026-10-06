# Plan: Use Builder Locks and Savepoints

## Created
2026-10-05

## Status
completed

## Objective
Replace the two pre-#176 workarounds (hand-written `FOR UPDATE SKIP LOCKED` in `DatabaseQueue`, hand-rolled transaction ownership in `RoleRepository::syncPermissions()`) with the query builder's row locks and the connection's nesting `transaction()`.

## Related Issues
Closes #224

## Discovery Notes
- Only `marko/database-mysql` and `marko/database-pgsql` exist as drivers; both support `SKIP LOCKED` and both bind `QueryBuilderFactoryInterface`. No SQLite driver ships, so `supportsSkipLocked()` has no production case to cover.
- Builders take their connection from the factory (bound to the shared `ConnectionInterface`), so the lock is taken in the same transaction `pop()` opens.
- The builder has no grouped `where`, so the `(reserved_at IS NULL OR reserved_at <= ?)` condition goes through `whereRaw()`.
- `queue-database` unit tests run against an in-memory SQLite fixture and PHPUnit mocks with named-binding assertions; the builder emits positional bindings and lock clauses SQLite rejects. The fixture will drop row-lock clauses (SQLite serializes writers with a database lock) and record them so tests can assert the lock was requested.
- `Repository::insertBatch()` already uses "`transaction()` when the connection supports it, else run directly". `syncPermissions()` will follow the same single fallback (the issue allows it) so the two methods in one class hierarchy behave the same.
- `#218` changes the queue packages in parallel; keep queue changes confined to `DatabaseQueue`, its module binding, tests and docs.

## Scope

### In Scope
- `DatabaseQueue::reserveNext()` via query builder `lockForUpdate()->skipLocked()`; delete `supportsSkipLocked()`; inject `QueryBuilderFactoryInterface` (module.php binding updated)
- `RoleRepository::syncPermissions()` via `transaction()`
- Tests: nested-transaction partial failure (RoleRepository), concurrent reservation (pgsql integration)
- Docs: queue-database.md, admin-auth.md

### Out of Scope
- PSR-20 clock adoption in DatabaseQueue (#221)
- Worker job serialization (#218)
- Converting the remaining raw INSERT/UPDATE/DELETE statements in DatabaseQueue to the builder

## Success Criteria
- [x] `grep -rn "FOR UPDATE\|SKIP LOCKED\|LOCK IN SHARE" packages/*/src` matches only the driver query builders (and interface docblocks)
- [x] `grep -rn "inTransaction()" packages/*/src` matches only connection/driver internals
- [x] Existing DatabaseQueue tests pass; pgsql round-trip passes; concurrent-reservation integration test added
- [x] RoleRepository nested-transaction test passes
- [x] Docs updated
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | RoleRepository::syncPermissions() uses transaction() | - | completed |
| 002 | DatabaseQueue reserves through the query builder lock | - | completed |
| 003 | PostgreSQL concurrent reservation integration test | 002 | completed |
| 004 | Docs for queue-database and admin-auth | 001, 002 | completed |

## Architecture Notes
- `DatabaseQueue` gains a required constructor parameter `QueryBuilderFactoryInterface $queryBuilderFactory`, placed immediately after `failedJobRepository`; the factory must build on the same connection the queue was given (true for the container wiring).
- A connection without `TransactionInterface` now fails `pop()` loudly with the builder's `LockException` instead of reading without a lock.

## Risks & Mitigations
- Parallel #218 edits to queue packages: keep diff minimal in `DatabaseQueue.php` and test files.
- Builder SQL on SQLite in tests: the fixture strips only lock clauses; everything else is real SQL.
