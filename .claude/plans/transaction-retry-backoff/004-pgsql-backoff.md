# Task 004: PgSqlConnection backoff between retries

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Inject `TransactionBackoff` into `PgSqlConnection` and wait between retry attempts of the outermost transaction.

## Context
- Related files: packages/database-pgsql/src/Connection/PgSqlConnection.php, packages/database-pgsql/src/Connection/PgSqlConnectionFactory.php, packages/database-pgsql/tests/Connection/PgSqlConnectionRetryTest.php, packages/database-pgsql/tests/Connection/PgSqlConnectionFactoryTest.php, packages/database-pgsql/tests/Module/DialectOverrideTest.php
- Constructor: append `private readonly TransactionBackoff $backoff = new TransactionBackoff(new UsleepSleeper(), new Randomizer())` AFTER `$exceptionTranslator` (many anonymous subclasses call `parent::__construct($config)`).
- The retry-test helper gains an optional `?TransactionBackoff` argument. Existing retry tests should pass `backoff: 0` or a FakeSleeper so they don't really sleep.
- `PgSqlConnectionFactory` takes a `TransactionBackoff` (autowired) and passes it to every connection.
- `DialectOverrideTest::buildVariantContainer()` registers only the pgsql manifest and resolves `ConnectionInterface` to `PgSqlConnection`. Once `TransactionBackoff` is a constructor dependency, that resolution throws (no `SleeperInterface` binding), so register `marko/database`'s bindings there too. Audit other container-built tests the same way.

## Requirements (Test Descriptions)
- [x] `it waits the default jittered exponential delay between attempts`
- [x] `it retries immediately when backoff is zero`
- [x] `it waits a fixed delay when backoff is an int`
- [x] `it waits the delay a closure returns`
- [x] `it waits before retrying a conflict raised by COMMIT`
- [x] `it never sleeps when attempts is one`
- [x] `it never sleeps in a nested transaction`
- [x] `it rejects a negative backoff before beginning the transaction`
- [x] `it never sleeps after the final failed attempt`
- [x] `it passes the TransactionBackoff to connections made by PgSqlConnectionFactory`
- [x] `it resolves PgSqlConnection with the bound TransactionBackoff from a container`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Mirror of 003 for PgSqlConnection and PgSqlConnectionFactory. DialectOverrideTest needed no change.
