# Plan: Shared Database Connection and Entity Hydrator

## Created
2026-10-05

## Status
completed

## Objective
Share one `ConnectionInterface` instance (and the `EntityHydrator`) per container so transactions span repositories, query builders run on the repository's connection, `TransactionInterface` is injectable, and dirty checking works across services.

## Related Issues
Closes #159

## Discovery Notes
- The container is transient by default; `database-pgsql`/`database-mysql` bind `ConnectionInterface` without marking it shared, so every consumer (each repository, each query builder factory, the queue) gets its own PDO handle.
- `TransactionInterface` is never bound by the driver modules; only `database-readwrite` registers it (via `instance()` in boot). `SeederRunner`'s `$container->has(TransactionInterface::class)` is therefore always false.
- `EntityHydrator` stores dirty-check snapshots in a per-instance `WeakMap`. With a non-shared hydrator, an auto-increment entity loaded by repository A and saved by repository B has no snapshot, so `getDirtyProperties()` returns `[]` and the UPDATE is silently skipped; a client-keyed entity is treated as new and INSERTed.
- `RelationshipLoader` holds no mutable state of its own (only injected deps, including the hydrator), so sharing the hydrator is sufficient.
- `Container::resolve()` checks `instances` first, so `database-readwrite`'s `instance()` calls in boot still override the driver's shared binding.
- Under `marko/roadrunner`, `WorkerRequestHandler` resets all resolved `ResettableInterface` instances between requests; a shared driver connection must roll back abandoned transactions there.
- No real-database integration tests exist and CI has no DB services, so the cross-repository rollback tests are env-gated (skip with a clear reason when no server is configured).

## Scope

### In Scope
- `singletons` entry for `ConnectionInterface` in both driver modules, and a `TransactionInterface` closure binding delegating to the shared connection (loud error if it does not implement `TransactionInterface`)
- `EntityHydrator` shared in `packages/database/module.php`
- `ResettableInterface` on `PgSqlConnection` and `MySqlConnection`
- Wiring tests built from the real module manifests (pgsql and mysql), readwrite-override test, SeederRunner test, cross-repository dirty-check test
- Env-gated integration tests against real PostgreSQL and MySQL
- Docs page and architecture doc updates

### Out of Scope
- Savepoints / nested transactions / after-commit callbacks (#176)
- Error-handling changes in the connection classes (#177)
- Binding concrete `PgSqlConnection`/`MySqlConnection` class ids to the shared instance (documented instead: depend on the interface)

## Success Criteria
- [x] Two repositories resolved from a container built from the real module manifests share one `ConnectionInterface`, which is also the one the `QueryBuilderFactoryInterface` uses
- [x] `TransactionInterface` resolves to that same instance
- [x] Writes through two repositories inside one `transaction()` roll back together (integration, pgsql and mysql)
- [x] An entity loaded by repository A and saved by repository B emits an UPDATE with only dirty columns
- [x] With `database-readwrite` enabled, both interfaces resolve to `ReadWriteConnection`
- [x] `reset()` rolls back an open transaction and is a no-op otherwise
- [x] `SeederRunner` receives a `TransactionInterface`
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Share connection and bind TransactionInterface in pgsql module | - | completed |
| 002 | Share connection and bind TransactionInterface in mysql module | - | completed |
| 003 | Share EntityHydrator; SeederRunner and cross-repository dirty-check tests | 001 | completed |
| 004 | Resettable PgSqlConnection and MySqlConnection | - | completed |
| 005 | database-readwrite still overrides the shared driver connection | 001, 002 | completed |
| 006 | Env-gated cross-repository transaction integration tests | 001, 002, 003 | completed |
| 007 | Documentation | 001, 002, 003, 004 | completed |

## Architecture Notes
- List-style singleton keeps the existing `bindings` entry, so the conflict detection in `BindingRegistry` is unchanged.
- `TransactionInterface` is a closure binding that re-fetches `ConnectionInterface` each time; it is not itself a singleton, so a later `instance()` override of `ConnectionInterface` is always honoured and the roadrunner reset loop does not see the same object under two keys.
- `reset()` must never open a connection: it checks the PDO handle directly instead of calling `inTransaction()` (which lazily connects).

## Risks & Mitigations
- Conflicts with #176/#177 in the connection classes: limit those edits to the `implements` clause and a single `reset()` method.
- Conflicts with #170/#178 in `packages/database/module.php`: one-line `singletons` addition only.
- Integration tests cannot run in CI: skip with an explicit reason naming the env vars; run locally against throwaway containers.
