# Devil's Advocate Review: database-test-refresh

## Critical (Must fix before building)

### C1. Task 007: the existing integration harness defeats "once per process"
`tests/Integration/App/Helpers.php` builds a new random temp project for every test (`buildIntegrationProject()`) and `bootIntegrationApp()` runs `DROP DATABASE ... WITH (FORCE)` on `integrationDatabaseName($env)` before every test. TestDatabase caches the booted app (and its open PDO connection) per process, keyed by base path. If task 007 reuses `setUpIntegrationTest()`:
- every test has a new base path, so "migrations run once per process" can't be shown;
- any other integration-services file running later in the same process drops the shared database and kills the cached connection, so later RefreshDatabase tests fail with a terminated connection or a missing schema.

Fix (applied to 007): a dedicated fixture (`tests/Integration/App/RefreshFixture/`) whose `config/database.php` uses `integrationDatabaseName($env) . '_refresh'`, keeping the TEST_TOKEN suffix so parallel workers stay separate. Drop and recreate that database once per process, and build the project once per process, both behind a static guard. Skip through `integrationServicesSkipReason()`, and remove the project at shutdown.

### C2. Tasks 003/007: unset APP_ENV means production, so TestDatabase refuses by default
Per #170, an unset `APP_ENV` counts as production. Test processes in this repo and in user apps usually don't set it, so `TestDatabase::boot()` will throw on every first use. That's correct behaviour, but:
- task 003's unit tests and task 007 must set `APP_ENV=testing` explicitly (`withIntegrationAppEnv()` or putenv, then restore). Otherwise every test fails with the production error, or it passes or fails depending on the developer's shell.
- task 008 must document `<env name="APP_ENV" value="testing"/>` in phpunit.xml as a required setup step.

Applied to 003, 007 and 008.

## Important (Should fix before building)

### I1. Tasks 003/_plan: where `client()` lives and what it excludes
In _plan.md, Risks puts it at `RefreshDatabase::client()`, while task 003 has `TestDatabase::client()`. The phrase "resolved transaction-capable connections" also leaves the exclusion set undefined. Fix: `TestDatabase::client()` returns `TestClient::forApplication($app)->withoutResetting(...)`, passing the instance from `$container->get(ConnectionInterface::class)` and from `get(TransactionInterface::class)`. Exclusion is by instance identity (`===`). For read/write setups this covers `ReadWriteConnection`: its `reset()` both rolls back the wrapped write connection and clears sticky-write. If it were reset, reads in later requests would go to replicas that can't see the uncommitted test data. Applied to 003 and _plan.md.

### I2. Task 003: "marko/database is not installed" can't be unit tested in the monorepo
`interface_exists(ConnectionInterface::class)` is always true here. Fix: put the guard behind a small static method that takes the interface name, `DatabaseTestException::databasePackageMissing()` is thrown when it's missing, and the test passes a non-existent name. Also fix the check order so nothing touches the database before refusing: package check, then environment check (`AppEnvironment` reads env vars and needs no boot), then `Application::boot`, then the driver-bound check (`container->has(ConnectionInterface::class)`, before any `get()` that would throw a generic BindingException), then migrate. Applied.

### I3. Task 001: ReadWriteConnection and callback semantics underspecified
- `ReadWriteConnection::$write` is typed `ConnectionInterface&TransactionInterface`, so it may not implement `PendingAfterCommitInterface`. ReadWrite always implements the interface, so RefreshDatabase's `instanceof` check can't catch this case. ReadWrite must throw a clear exception itself when its write connection doesn't support it.
- Each callback should be removed before it runs, matching `TransactionState::commit()`, which pops before running. A callback that throws then can't run twice.
- Callbacks registered while the run is in progress land on the innermost open level. They aren't run by the same call.
- Order: outermost level first, registration order within a level. That's the order a real commit would produce.

Applied.

### I4. Task 004: a failed teardown rollback poisons every later test
If `rollback()` throws (connection dropped, server error), the connection keeps level > 0. Every later test then fails with "a test transaction is already open", which hides the real failure. Fix: when rollback fails, call `reset()` on a `ResettableInterface` connection and rethrow. Also:
- Spell out the API: `new RefreshDatabase(TestDatabase $database)`, `begin()`, `rollback()`, `runAfterCommitCallbacks()`. Task 007 and the docs build against it.
- After-rollback callbacks registered by code under test run during teardown. Test and document this.

Applied.

### I5. Task 005: MySQL branch has no coverage
Integration runs Postgres only, and task 005's requirements don't cover the SQL each driver emits. Add unit requirements with a fake connection that assert the exact statements: the PG single `TRUNCATE ... RESTART IDENTITY CASCADE`, and MySQL `SET FOREIGN_KEY_CHECKS=0`/per-table `TRUNCATE`/`=1`, with the re-enable in a `finally`. Also: an empty table list runs no SQL, the migrations table is never included, and table discovery reuses `EntityDiscovery::discoverAll()` the way `MigrateCommand` does. Applied.

### I6. Task 006: contract gaps a worker will guess at
- Constructor: `__construct(?ContainerInterface $container = null)`.
- Missing `REPOSITORY`: a missing constant is fatal, so check with `defined(static::class . '::REPOSITORY')` and throw a clear exception.
- Call `definition()` once per entity. Never clone, or `makeMany` returns shared state.
- State signature: `callable(TEntity): void`. States mutate the entity.
- `sequence()` returns a new factory and doesn't change the receiver.
- `create()` calls `RepositoryInterface::save()`, which fires `EntityCreating`/`EntityCreated`.
- Add `@template TEntity of Entity` for PHPStan.

Applied.

## Minor (Nice to address)
- Postgres aborted-transaction gotcha: if code under test hits a SQL error outside its own `transaction()`, the outer test transaction is aborted and later queries in the same test fail. Mention it in the testing.md note (008).
- TruncateDatabase only covers entity-backed tables. Tables created only by raw migrations (for example a queue jobs table, if it isn't an entity) are kept. Document this.
- `Application::boot()` with errors-simple installs global error/exception handlers. The integration harness restores them; TestDatabase (and the existing `TestClient::boot`) do not. Consider restoring them in TestDatabase so assertion failures aren't rendered as error pages.
- Calling `boot($path, fresh: true)` after `boot($path)` in the same process: decide whether to ignore it or throw. Throwing is more in line with "loud errors".
- `.claude/testing.md` could mention the new integration file and its dedicated database.

## Questions for the Team
- `fresh: true` and `TruncateDatabase` refuse only production. With `APP_ENV=local`, which is common in a developer's `.env`, they will wipe the development database. Should these destructive paths require `APP_ENV=testing` (or a non-production environment that isn't development)?
- Should `TestDatabase` restore global error handlers after boot (see Minor)? It's a behavioural choice that `TestClient::boot` would arguably share.
