# Plan: Transaction Retry Backoff

## Created
2026-10-05

## Status
completed

## Objective
Give `transaction(attempts:)` a jittered exponential backoff between retries (overridable with an `int` or a `Closure`), and make MariaDB's snapshot-isolation conflict (error 1020) a retryable `SerializationFailureException`.

## Related Issues
Closes #257

## Discovery Notes
- `transaction()` retry loops live in `MySqlConnection` and `PgSqlConnection` (identical shape); `ReadWriteConnection` delegates to the write connection.
- ~20 test fixtures implement `TransactionInterface::transaction()`; all need the new parameter.
- The container autowires every class-typed constructor parameter (defaults are ignored for class types), so a new constructor dependency on the drivers must be resolvable: `SleeperInterface` and `TransactionBackoff` are bound in `marko/database`'s `module.php`. `Random\Randomizer` (jitter source) is not autowirable (`?Engine` parameter), so `TransactionBackoff` is bound with a closure.
- `MySqlExceptionTranslator` maps 1213/1205/3572; 1020 falls through to `QueryException`.
- CI runs MySQL 8.4 (not MariaDB). MySQL never raises 1020 for a REPEATABLE READ write conflict, so the optional MariaDB integration test is left as a follow-up (needs a MariaDB CI service).
- #221 (clock adoption) is unrelated: this waits rather than reading time; PSR-20 has no sleep.

## Scope

### In Scope
- `SleeperInterface` + `UsleepSleeper` + `TransactionBackoff` in `marko/database`
- `transaction(callable $callback, int $attempts = 1, int|Closure|null $backoff = null)` on the interface, both drivers, `ReadWriteConnection`, and all test fixtures
- `FakeSleeper` in `marko/testing`
- 1020 → `SerializationFailureException` in `MySqlExceptionTranslator`
- Docs: database.md (Retrying a Transaction, concurrency tables), database-mysql.md (`innodb_snapshot_isolation`), testing.md (`FakeSleeper`)

### Out of Scope
- MariaDB CI service and a real 1020 integration test (follow-up)
- Backoff configuration through config files (per-call argument only)

## Success Criteria
- [x] Default backoff follows `Randomizer::getInt(0, min(500, 10 * 2 ** (attempt - 1)))` ms, asserted via FakeSleeper with a seeded Randomizer
- [x] `backoff: 0` retries immediately, `int` and `Closure` honoured, negative values fail loudly
- [x] Nested transaction and `attempts: 1` never sleep
- [x] ReadWriteConnection passes backoff through
- [x] 1020 maps to SerializationFailureException and is retried
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Backoff seam, interface signature, implementer signatures in marko/database | - | completed |
| 002 | FakeSleeper in marko/testing | 001 | completed |
| 003 | MySqlConnection (+ factory) backoff between retries | 001, 002 | completed |
| 004 | PgSqlConnection (+ factory) backoff between retries | 001, 002 | completed |
| 005 | ReadWriteConnection passes backoff through | 001, 002, 003 | completed |
| 006 | Retry test for MariaDB 1020 (mapping already exists) | 002, 003 | completed |
| 007 | Documentation | 001-006 | completed |

## Interface Contract (shared by 001-005)
- `Marko\Database\Connection\SleeperInterface::sleep(int $milliseconds): void`
- `Marko\Database\Connection\UsleepSleeper implements SleeperInterface` (`usleep($milliseconds * 1000)`; no-op for 0)
- `Marko\Database\Connection\TransactionBackoff`:
  - `__construct(SleeperInterface $sleeper = new UsleepSleeper(), Randomizer $randomizer = new Randomizer())` (defaults for direct construction; bound by closure in `marko/database/module.php`: `new TransactionBackoff($container->get(SleeperInterface::class), new Randomizer())`; `SleeperInterface => UsleepSleeper`)
  - `validate(int|Closure|null $backoff): void` (throws for negative int)
  - `wait(int $attempt, int|Closure|null $backoff, TransactionConflictException $conflict): void` (computes delay, throws for negative/non-int closure result, calls `sleep()` exactly once, including with 0)
- Exceptions: `TransactionException::invalidBackoff(int $milliseconds)` and `TransactionException::invalidBackoffDelay(mixed $delay)`
- Driver constructors: append `private readonly TransactionBackoff $transactionBackoff = new TransactionBackoff()` AFTER `$exceptionTranslator`, so existing `parent::__construct($config)` / named `exceptionTranslator:` call sites keep working. The container ignores this default and resolves through the module binding.
- `MySqlConnectionFactory` / `PgSqlConnectionFactory` take `TransactionBackoff` and pass it to every connection they make, so the read/write write connection honours container overrides.
- `FakeSleeper` (`Marko\Testing\Fake\FakeSleeper`): public `array $sleeps` (list<int>), `clear()`, `assertSlept(int ...$milliseconds): void`, `assertNotSlept(): void`
- Tests build `new TransactionBackoff($fakeSleeper, new Randomizer(new Mt19937(<seed>)))` to get deterministic delays.

## Architecture Notes
- `null` backoff = default jittered exponential (base 10 ms, cap 500 ms, full jitter); `int` = fixed ms; `Closure(int $attempt, TransactionConflictException $conflict): int` = custom ms. `$attempt` is the 1-based number of the attempt that just failed.
- The sleeper is called once per retry (including with 0), never on the final failure, never for nested calls.
- Backoff is validated up front (before BEGIN), so a negative int fails loudly even when no retry happens.

## Risks & Mitigations
- Container cannot autowire `TransactionBackoff` without bindings: bind in `marko/database/module.php`; driver tests that build containers load that module. Known offender: `database-pgsql/tests/Module/DialectOverrideTest.php` registers only the pgsql manifest (fixed in 004).
- Changing `TransactionInterface` fatals every implementer with the old signature: 001 updates the three production implementers' signatures and all test fixtures in the same task.
- `MySqlExceptionTranslator` already maps 1020 (with a translator test); 006 only adds the retry-level test.
- Existing retry tests now really sleep a few ms: acceptable (default delays ≤ 30 ms per test).
