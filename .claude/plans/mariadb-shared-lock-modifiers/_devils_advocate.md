# Devil's Advocate Review: mariadb-shared-lock-modifiers

## Critical (Must fix before building)
- **002**: The container autowires every class-typed constructor parameter, even nullable ones with a `= null` default (`Container::resolve()` lines 216-240 never check `allowsNull`/default for class types). A `MySqlServer(ConnectionInterface $connection, ?MySqlServerVersion $version = null)` seam would make the container try to build `MySqlServerVersion` (scalar ctor params without defaults) and throw `BindingException`. MySqlServer's constructor must take only `ConnectionInterface`.
- **003 / 004**: Existing unit-test mock connections return `[]` for unmatched SQL. Once detection goes through `MySqlServer`, an empty version throws `ServerVersionException` (where `isMariaDb()` used to return false). This breaks `MySqlQueryBuilderLockingUpsertTest` (lines 47-63, `FOR SHARE` modifier tests) and every `MySqlIntrospectorTest`/`MySqlExpressionDefaultMatcherTest` case whose mock has no `VERSION()` entry. The tasks must update those fixtures.

## Important (Should fix before building)
- **003 / 004**: Fallback when no `MySqlServer` is passed is undefined. Many callers (`new MySqlQueryBuilder($conn)`, `new MySqlIntrospector($conn, 'db')`, queue-database integration tests) pass none. Specify: optional trailing `?MySqlServer $server = null`, resolved lazily as `$this->server ??= new MySqlServer($this->connection)` only when needed (the builder cannot use promoted readonly for a lazily-assigned property).
- **002**: `MySqlServer` must keep the exact SQL `SELECT VERSION() AS version` so existing mocks keyed on `VERSION()` and IntegrationDatabase stay consistent.
- **001**: `MySqlServerVersion` and `ServerVersionException` already exist in the worktree. The worker should check them against the requirements, not recreate them.
- **004**: The `createMockConnection` helper is a `readonly` anonymous class, so it cannot count calls. The "reads the server version once" test needs its own counting connection.

## Minor (Nice to address)
- Through `ReadWriteConnection`, `SELECT VERSION()` goes to a replica outside a transaction. That is correct, but it adds a replica round trip (and a possible replica connection error) to the first shared-lock query.
- If anything resolves `MySqlServer` before database-readwrite's boot swaps `ConnectionInterface`, the service holds the raw write connection. That still works (same server type).
- 007 is optional and judgement-based. An autonomous worker may need a human call on what counts as a "small" time increase.

## Questions for the Team
- Should `toSql()`-style compilation (no execution) be allowed to hit the database for detection? Under the current design it will, for shared-lock-with-modifier queries.
