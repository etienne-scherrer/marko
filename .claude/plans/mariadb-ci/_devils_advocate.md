# Devil's Advocate Review: mariadb-ci

## Critical (Must fix before building)

1. **Task 001 defines no API, and nothing invokes the server check (001, 003, 004, 005).** Tasks 003 and 004 need "is this MariaDB?" from the fixture, but 001 names no method. `IntegrationDatabase::config()` never connects, so it can't check the server. If the mismatch check lives only in a helper that tests call when they want to, a mispointed MariaDB step still passes: every MariaDB-only test skips, which is exactly the risk the plan says it mitigates. Fix: specify `isMariaDbVersion(string)`, `isMariaDb(ConnectionInterface)` and `assertServer(string $version, ?array $env)`. Add a `tests/Integration/ServerIdentityTest.php` that connects and calls `assertServer`, so both CI steps prove which server they hit.
2. **Task 005 doesn't spell out the CI env wiring.** The job-level env pins `MARKO_TEST_MYSQL_PORT: 3306`. The MariaDB step has to override `MARKO_TEST_MYSQL_PORT=3307` and `MARKO_TEST_MYSQL_SERVER=mariadb` at step level. The job level should set `MARKO_TEST_MYSQL_SERVER: mysql` so the existing `composer test:integration` step also proves its server. The targeted Pest call needs `--group=integration-services` (or an explicit path) and `-c phpunit.xml`. `deadlock-contender.php` reads `MARKO_TEST_MYSQL_PORT` through `getenv()`, so step-level env works for it; a hardcoded port would not.

## Important (Should fix before building)

3. **Task 003 has timing traps.** Under REPEATABLE READ the snapshot is taken at the first consistent read, not at `BEGIN`. The test must SELECT the row inside the transaction before the other connection commits its change, or no 1020 is raised. Don't rely on server defaults: set `SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ` and `SET SESSION innodb_snapshot_isolation = ON` explicitly. In the retry test, only attempt 1 may provoke the conflict, and the test should assert `attempts === 2` and the final row value.
4. **Task 002 leaves out length and match rules.** MariaDB reports `CHARACTER_MAXIMUM_LENGTH = 4294967295` and collation `utf8mb4_bin` for JSON. The JSON column must get `length: null` and `collation: null`. The `json_valid` clause must match the exact column (`meta` must not match `meta2`, and a compound clause must not match). Query CHECK_CONSTRAINTS at most once per `getColumns()` call, bound by schema and table, and only when the server is MariaDB and there is a `longtext` column (no N+1). The `on update current_timestamp(3)` case keeps its precision. The integration test runs on both servers, so it doesn't depend on 001. Note: `mariaDbLongtextColumns()` already exists in `MySqlIntrospectorTest.php`; reuse it.
5. **Task 005 also depends on 002.** The docs describe the JSON behaviour that 002 implements.
6. **Task 004 must skip and select tests through 001's API.** It should use `IntegrationDatabase::isMariaDb()`; nothing else should sniff versions ad hoc. Each server-specific test should skip on the other server with a reason that names the difference.

## Minor (Nice to address)

- `isMariaDb()` in the introspector already sends `SELECT VERSION()` on every `getColumns()` call (existing behaviour). Adding CHECK_CONSTRAINTS makes that two extra queries per table.
- On MariaDB, a user-declared `LONGTEXT CHECK (json_valid(col))` is indistinguishable from `JSON` and will be reported as `json`. That is acceptable, but it should be documented.
- Update the comment block above the CI `integration` job to mention MariaDB.

## Questions for the Team

- Should the MariaDB Pest step also pass `--parallel`? It's probably not worth it for about 10s of tests, and the concurrency tests use a shared table.
